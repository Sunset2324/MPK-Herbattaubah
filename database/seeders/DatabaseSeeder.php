<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** Data wajib: pengaturan default dan produk awal. Data demo: php artisan db:seed --class=DemoSeeder */
    public function run(): void
    {
        $this->call([SettingSeeder::class, ProductSeeder::class]);
    }
}
