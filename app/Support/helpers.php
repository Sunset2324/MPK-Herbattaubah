<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    function setting(string $key, ?string $default = null): string
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('rupiah')) {
    function rupiah($n): string
    {
        return 'Rp '.number_format((float) $n, 0, ',', '.');
    }
}

if (! function_exists('wa_link')) {
    function wa_link(?string $phone, string $text = ''): string
    {
        $p = preg_replace('/\D/', '', (string) $phone);
        if (str_starts_with($p, '0')) {
            $p = '62'.substr($p, 1);
        }

        return 'https://wa.me/'.$p.($text !== '' ? '?text='.rawurlencode($text) : '');
    }
}

if (! function_exists('img_src')) {
    function img_src(?string $path): string
    {
        if (! $path) {
            return asset('assets/img/no-image.svg');
        }
        if (preg_match('~^https?://~', $path)) {
            return $path;
        }

        return asset($path);
    }
}

if (! function_exists('month_label')) {
    function month_label(string $ym): string
    {
        $bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        return $bulan[(int) substr($ym, 5, 2)].' '.substr($ym, 0, 4);
    }
}

if (! function_exists('valid_month')) {
    function valid_month(?string $ym): string
    {
        return preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $ym) ? $ym : now()->format('Y-m');
    }
}

if (! function_exists('month_options')) {
    /** @return array<string,string> YYYY-MM => label, 24 bulan terakhir */
    function month_options(int $back = 24): array
    {
        $out = [];
        for ($i = 0; $i < $back; $i++) {
            $m = now()->startOfMonth()->subMonths($i)->format('Y-m');
            $out[$m] = month_label($m);
        }

        return $out;
    }
}

if (! function_exists('money_input')) {
    /** "Rp 1.000.000" -> 1000000, kosong -> null */
    function money_input($v): ?int
    {
        $d = preg_replace('/\D/', '', (string) $v);

        return $d === '' ? null : (int) $d;
    }
}
