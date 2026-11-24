<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Purchase;
use Carbon\Carbon;

/**
 * Perhitungan bonus bulanan MPK.
 *
 * Aturan diambil dari rumus di file "Simulasi MPK Januari.xlsx":
 *   PERSONAL = total belanja member dalam bulan itu
 *   GROUP    = PERSONAL + total PERSONAL downline (sedalam 'group_depth' level; 1 = downline langsung, 0 = semua level)
 *   BONUS    = GROUP x bonus_pct%      bila PERSONAL >= min_personal
 *   VOUCHER  = voucher_amount          bila GROUP >= voucher_min_group
 *   ROYALTY  = royalty_pct% x total PERSONAL seluruh member. Member yang dapat VOUCHER menerima
 *              (BONUS dia / total BONUS semua member) x pool royalty. Sisa pool tidak dibagikan
 *              (sama seperti rumus di Excel).
 *   TOTAL    = BONUS + VOUCHER + ROYALTY
 *
 * Semua angka bisa diubah admin di menu Pengaturan.
 */
class BonusCalculator
{
    /** @return array<string,int|float> */
    public static function rules(): array
    {
        return [
            'min_personal' => (int) setting('bonus_min_personal'),
            'bonus_pct' => (float) setting('bonus_pct'),
            'voucher_min_group' => (int) setting('voucher_min_group'),
            'voucher_amount' => (int) setting('voucher_amount'),
            'royalty_pct' => (float) setting('royalty_pct'),
            'group_depth' => (int) setting('group_depth'),
        ];
    }

    /**
     * @param  string  $month  format YYYY-MM
     * @return array{rows: list<array<string,mixed>>, totals: array<string,int>, rules: array<string,int|float>}
     */
    public function compute(string $month, ?array $rules = null): array
    {
        $rules ??= static::rules();
        $start = Carbon::createFromFormat('Y-m-d', $month.'-01')->startOfDay();
        $end = $start->copy()->endOfMonth();

        $members = Member::query()->get(['id', 'member_code', 'name', 'sponsor_id', 'status']);
        $personal = Purchase::query()
            ->whereBetween('purchase_date', [$start->toDateString(), $end->toDateString()])
            ->groupBy('member_id')
            ->selectRaw('member_id, SUM(amount) AS total')
            ->pluck('total', 'member_id')
            ->map(fn ($v) => (int) $v)
            ->all();

        $rows = [];
        $children = [];
        foreach ($members as $m) {
            $rows[$m->id] = [
                'id' => $m->id,
                'member_code' => $m->member_code,
                'name' => $m->name,
                'sponsor_id' => $m->sponsor_id,
                'status' => $m->status,
                'personal' => $personal[$m->id] ?? 0,
                'downlines' => 0,
            ];
            if ($m->sponsor_id) {
                $children[$m->sponsor_id][] = $m->id;
            }
        }

        $depth = $rules['group_depth'] > 0 ? $rules['group_depth'] : PHP_INT_MAX;
        foreach ($rows as $id => &$r) {
            $r['downlines'] = count($children[$id] ?? []);
            // jumlahkan personal downline sampai kedalaman tertentu (BFS, aman dari siklus)
            $group = $r['personal'];
            $frontier = $children[$id] ?? [];
            $seen = [$id => true];
            for ($lvl = 1; $frontier && $lvl <= $depth; $lvl++) {
                $next = [];
                foreach ($frontier as $c) {
                    if (isset($seen[$c]) || ! isset($rows[$c])) {
                        continue;
                    }
                    $seen[$c] = true;
                    $group += $personal[$c] ?? 0;
                    foreach ($children[$c] ?? [] as $gc) {
                        $next[] = $gc;
                    }
                }
                $frontier = $next;
            }
            $r['group'] = $group;
        }
        unset($r);

        $totalPersonal = 0;
        $totalBonus = 0;
        foreach ($rows as &$r) {
            $totalPersonal += $r['personal'];
            $r['bonus'] = $r['personal'] >= $rules['min_personal'] ? (int) round($r['group'] * $rules['bonus_pct'] / 100) : 0;
            $r['voucher'] = $r['group'] >= $rules['voucher_min_group'] ? (int) $rules['voucher_amount'] : 0;
            $totalBonus += $r['bonus'];
        }
        unset($r);

        $royaltyPool = (int) round($totalPersonal * $rules['royalty_pct'] / 100);
        $sum = ['personal' => $totalPersonal, 'bonus' => 0, 'voucher' => 0, 'royalty' => 0, 'total' => 0, 'royalty_pool' => $royaltyPool];
        foreach ($rows as &$r) {
            $r['royalty'] = ($r['voucher'] > 0 && $totalBonus > 0)
                ? (int) round($r['bonus'] / $totalBonus * $royaltyPool)
                : 0;
            $r['total'] = $r['bonus'] + $r['voucher'] + $r['royalty'];
            foreach (['bonus', 'voucher', 'royalty', 'total'] as $k) {
                $sum[$k] += $r[$k];
            }
        }
        unset($r);

        $rows = array_values(array_filter($rows, fn ($r) => $r['personal'] > 0 || $r['group'] > 0));
        usort($rows, fn ($a, $b) => [$b['total'], $b['group']] <=> [$a['total'], $a['group']]);

        return ['rows' => $rows, 'totals' => $sum, 'rules' => $rules];
    }
}
