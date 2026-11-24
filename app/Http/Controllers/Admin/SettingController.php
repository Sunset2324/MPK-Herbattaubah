<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public const FIELDS = [
        'Profil' => [
            'site_name' => ['Nama situs', 'text'],
            'long_name' => ['Kepanjangan', 'text'],
            'tagline' => ['Tagline', 'text'],
            'about' => ['Tentang kami (pisahkan paragraf dengan baris kosong)', 'textarea'],
            'training' => ['Deskripsi pelatihan', 'textarea'],
        ],
        'Kontak' => [
            'address' => ['Alamat', 'text'],
            'whatsapp' => ['Nomor WhatsApp', 'text'],
            'phone' => ['Telepon', 'text'],
            'email' => ['Email', 'email'],
            'instagram' => ['Link Instagram', 'url'],
            'facebook' => ['Link Facebook', 'url'],
            'maps_embed' => ['Link embed Google Maps (opsional, diawali https://www.google.com/maps/embed?)', 'url'],
        ],
        'Aturan bonus' => [
            'bonus_min_personal' => ['Minimal belanja personal agar dapat bonus (Rp)', 'number'],
            'bonus_pct' => ['Persentase bonus dari omzet group (%)', 'number'],
            'voucher_min_group' => ['Minimal omzet group untuk voucher (Rp)', 'number'],
            'voucher_amount' => ['Nilai voucher (Rp)', 'number'],
            'royalty_pct' => ['Royalty: persen dari total omzet (%)', 'number'],
            'group_depth' => ['Kedalaman omzet group (1 = downline langsung saja, 0 = semua level)', 'number'],
        ],
    ];

    public function edit()
    {
        return view('admin.settings', ['fields' => self::FIELDS]);
    }

    public function update(Request $request)
    {
        $rules = [];
        foreach (self::FIELDS as $group) {
            foreach ($group as $key => [$label, $type]) {
                $rules[$key] = match ($type) {
                    'email' => ['nullable', 'email'],
                    'url' => ['nullable', 'url'],
                    'number' => ['nullable', 'numeric', 'min:0'],
                    default => ['nullable', 'string'],
                };
            }
        }
        foreach (array_keys(self::FIELDS['Aturan bonus']) as $k) {
            if ($request->filled($k)) {
                $request->merge([$k => str_replace(',', '.', (string) $request->input($k))]);
            }
        }
        $data = $request->validate($rules);
        foreach ($data as $k => $v) {
            if (isset(self::FIELDS['Aturan bonus'][$k])) {
                $v = (string) (0 + ($v ?: 0));
            }
            Setting::put($k, $v === null ? '' : (string) $v);
        }

        return redirect()->route('admin.settings')->with('ok', 'Pengaturan disimpan.');
    }
}
