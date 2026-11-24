<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Setting;
use App\Services\BonusCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BonusCalculatorTest extends TestCase
{
    use RefreshDatabase;

    private function member(string $code, ?Member $sponsor = null): Member
    {
        return Member::create(['member_code' => $code, 'name' => $code, 'sponsor_id' => $sponsor?->id, 'status' => 'aktif']);
    }

    private function buy(Member $m, int $amount, string $date = '2026-01-10'): void
    {
        $m->purchases()->create(['purchase_date' => $date, 'amount' => $amount]);
    }

    private function row(array $res, string $code): array
    {
        return collect($res['rows'])->firstWhere('member_code', $code);
    }

    public function test_rumus_sesuai_simulasi_excel(): void
    {
        // A -> B, C ; B -> D
        $a = $this->member('A');
        $b = $this->member('B', $a);
        $c = $this->member('C', $a);
        $d = $this->member('D', $b);

        $this->buy($a, 1_000_000);
        $this->buy($b, 2_500_000);
        $this->buy($c, 2_000_000);
        $this->buy($d, 500_000);
        $this->buy($a, 9_000_000, '2026-02-01'); // bulan lain, tidak dihitung

        $res = (new BonusCalculator)->compute('2026-01');

        // A: group = 1jt + 2,5jt + 2jt (downline langsung saja) = 5,5jt
        $ra = $this->row($res, 'A');
        $this->assertSame(1_000_000, $ra['personal']);
        $this->assertSame(5_500_000, $ra['group']);
        $this->assertSame(550_000, $ra['bonus']);
        $this->assertSame(1_000_000, $ra['voucher']);
        $this->assertSame(2, $ra['downlines']);

        // B: group = 2,5jt + 0,5jt = 3jt, bonus 300rb, tanpa voucher
        $rb = $this->row($res, 'B');
        $this->assertSame(3_000_000, $rb['group']);
        $this->assertSame(300_000, $rb['bonus']);
        $this->assertSame(0, $rb['voucher']);
        $this->assertSame(0, $rb['royalty']);

        // D: personal < 1jt -> tanpa bonus
        $this->assertSame(0, $this->row($res, 'D')['bonus']);

        // royalty pool = 5% x 6jt = 300rb; A satu-satunya penerima voucher,
        // bagiannya = bonus A / total bonus (550 + 300 + 200) x 300rb
        $this->assertSame(300_000, $res['totals']['royalty_pool']);
        $this->assertSame((int) round(550_000 / 1_050_000 * 300_000), $ra['royalty']);
        $this->assertSame($ra['bonus'] + $ra['voucher'] + $ra['royalty'], $ra['total']);
        $this->assertSame('A', $res['rows'][0]['member_code']);
    }

    public function test_kedalaman_group_semua_level(): void
    {
        Setting::put('group_depth', '0');
        $a = $this->member('A');
        $b = $this->member('B', $a);
        $c = $this->member('C', $b);
        $this->buy($c, 700_000);

        $res = (new BonusCalculator)->compute('2026-01');

        $this->assertSame(700_000, $this->row($res, 'A')['group']);
        $this->assertSame(700_000, $this->row($res, 'B')['group']);
    }
}
