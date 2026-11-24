<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /** Isi pengaturan default (diambil dari website lama) tanpa menimpa yang sudah diubah admin. */
    public function run(): void
    {
        foreach (Setting::DEFAULTS as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
        Setting::flushCache();
    }
}
