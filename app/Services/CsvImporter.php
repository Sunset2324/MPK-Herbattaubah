<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Impor member dan belanja dari CSV (hasil "Save As CSV" di Excel, pemisah , atau ;). */
class CsvImporter
{
    /** @return list<array<string,string>> baris asosiatif, header huruf kecil */
    public static function read(string $path): array
    {
        $content = (string) file_get_contents($path);
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $lines = preg_split('/\r\n|\n|\r/', trim($content));
        if (! $lines || trim($lines[0]) === '') {
            throw new RuntimeException('File CSV kosong.');
        }
        $sep = substr_count($lines[0], ';') > substr_count($lines[0], ',') ? ';' : ',';
        $header = array_map(fn ($h) => strtolower(trim($h)), str_getcsv(array_shift($lines), $sep));
        $rows = [];
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $vals = str_getcsv($line, $sep);
            $rows[] = array_combine($header, array_pad(array_slice($vals, 0, count($header)), count($header), ''));
        }

        return $rows;
    }

    public static function normDate(?string $v): ?string
    {
        $v = trim((string) $v);
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $v, $m) && checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
            return sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
        }
        if (preg_match('~^(\d{1,2})[/-](\d{1,2})[/-](\d{4})$~', $v, $m) && checkdate((int) $m[2], (int) $m[1], (int) $m[3])) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }

        return null;
    }

    /** @return list<string> laporan; baris pertama ringkasan */
    public function importMembers(array $rows): array
    {
        return DB::transaction(function () use ($rows) {
            $report = [];
            $added = $updated = 0;
            $links = [];
            foreach ($rows as $i => $r) {
                $code = strtoupper(trim($r['kode'] ?? ''));
                $name = trim($r['nama'] ?? '');
                if ($code === '' || $name === '') {
                    $report[] = 'Baris '.($i + 2).': kode atau nama kosong, dilewati.';

                    continue;
                }
                $data = [
                    'name' => $name,
                    'phone' => trim($r['hp'] ?? '') ?: null,
                    'email' => trim($r['email'] ?? '') ?: null,
                    'address' => trim($r['alamat'] ?? '') ?: null,
                    'status' => trim($r['status'] ?? '') === 'nonaktif' ? 'nonaktif' : 'aktif',
                ];
                $joined = self::normDate($r['tgl_gabung'] ?? '');
                $existing = Member::where('member_code', $code)->first();
                if ($existing) {
                    $existing->update($data + ($joined ? ['joined_at' => $joined] : []));
                    $updated++;
                } else {
                    Member::create($data + ['member_code' => $code, 'joined_at' => $joined]);
                    $added++;
                }
                if (trim($r['kode_sponsor'] ?? '') !== '') {
                    $links[$code] = strtoupper(trim($r['kode_sponsor']));
                }
            }
            // tahap 2: hubungkan sponsor (sponsor boleh muncul di baris mana saja)
            foreach ($links as $code => $spCode) {
                $sp = Member::where('member_code', $spCode)->first();
                $me = Member::where('member_code', $code)->first();
                if (! $sp) {
                    $report[] = "$code: kode sponsor $spCode tidak ditemukan.";
                } elseif (Member::wouldCreateCycle($me->id, $sp->id)) {
                    $report[] = "$code: sponsor $spCode membuat jaringan melingkar, dilewati.";
                } else {
                    $me->update(['sponsor_id' => $sp->id]);
                }
            }
            array_unshift($report, "$added member baru, $updated diperbarui.");

            return $report;
        });
    }

    /** @return list<string> */
    public function importPurchases(array $rows): array
    {
        return DB::transaction(function () use ($rows) {
            $report = [];
            $added = 0;
            $ids = Member::pluck('id', 'member_code')->all();
            foreach ($rows as $i => $r) {
                $memberId = $ids[strtoupper(trim($r['kode'] ?? ''))] ?? null;
                $date = self::normDate($r['tanggal'] ?? '');
                $amount = (int) money_input($r['nominal'] ?? '');
                if (! $memberId || ! $date || $amount <= 0) {
                    $report[] = 'Baris '.($i + 2).': data tidak lengkap atau kode member tidak dikenal, dilewati.';

                    continue;
                }
                Purchase::create([
                    'member_id' => $memberId,
                    'purchase_date' => $date,
                    'amount' => $amount,
                    'note' => trim($r['keterangan'] ?? '') ?: 'Impor CSV',
                ]);
                $added++;
            }
            array_unshift($report, "$added transaksi belanja diimpor.");

            return $report;
        });
    }
}
