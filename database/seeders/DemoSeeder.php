<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Purchase;
use Illuminate\Database\Seeder;

/** Data contoh (fiktif): 60 member dan transaksi 2 bulan terakhir, untuk mencoba admin. */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (Member::exists()) {
            $this->command?->warn('Tabel member sudah berisi data, data demo dilewati.');

            return;
        }

        mt_srand(7);
        $first = ['Ahmad', 'Siti', 'Budi', 'Nur', 'Rina', 'Hasan', 'Aisyah', 'Fajar', 'Dewi', 'Yusuf', 'Lina', 'Rizki', 'Fitri', 'Hendra', 'Maya', 'Ilham', 'Sari', 'Umar', 'Laila', 'Bayu'];
        $last = ['Hidayat', 'Rahmawati', 'Saputra', 'Lestari', 'Pratama', 'Wulandari', 'Nugroho', 'Kurniawan', 'Fauziah', 'Ramadhan'];
        $cities = ['Bogor', 'Depok', 'Bekasi', 'Tangerang', 'Jakarta Timur', 'Serang', 'Sukabumi', 'Tasikmalaya', 'Cianjur', 'Karawang'];

        $root = Member::create(['member_code' => 'MPK0000001', 'name' => 'MPK Attaubah (Pusat)', 'address' => 'Bogor', 'status' => 'aktif', 'joined_at' => '2023-01-01']);
        $ids = [$root->id];
        for ($i = 2; $i <= 60; $i++) {
            $sponsor = $ids[min(count($ids) - 1, (int) floor(mt_rand(0, 1000) / 1000 * min(count($ids), 1 + intdiv($i, 3))))];
            $ids[] = Member::create([
                'member_code' => sprintf('MPK%07d', $i),
                'name' => $first[mt_rand(0, 19)].' '.$last[mt_rand(0, 9)],
                'phone' => '08'.mt_rand(11, 99).mt_rand(1000000, 9999999),
                'address' => $cities[mt_rand(0, 9)],
                'sponsor_id' => $sponsor,
                'status' => mt_rand(0, 9) ? 'aktif' : 'nonaktif',
                'joined_at' => now()->subDays(mt_rand(10, 600))->toDateString(),
            ])->id;
        }

        foreach ([now()->startOfMonth()->subMonth(), now()->startOfMonth()] as $month) {
            foreach ($ids as $id) {
                if (mt_rand(0, 2) === 0) {
                    continue;
                }
                for ($k = 0, $n = mt_rand(1, 3); $k < $n; $k++) {
                    Purchase::create([
                        'member_id' => $id,
                        'purchase_date' => $month->copy()->day(mt_rand(1, 28))->toDateString(),
                        'amount' => [250000, 500000, 1000000, 1500000][mt_rand(0, 3)],
                        'note' => 'Data contoh',
                    ]);
                }
            }
        }

        $this->command?->info('Data contoh: '.count($ids).' member + transaksi dibuat.');
    }
}
