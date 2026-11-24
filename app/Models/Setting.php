<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Throwable;

class Setting extends Model
{
    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['key', 'value'];

    /** @var array<string,string>|null */
    protected static ?array $cache = null;

    public const DEFAULTS = [
        'site_name' => 'MPK Herbattaubah',
        'tagline' => 'Modal Kecil, Penghasilan Besar',
        'long_name' => 'Management Pengembangan Karir',
        'about' => 'MPK Herbattaubah (Management Pengembangan Karir) adalah bisnis produk herbal dan pelatihan penyembuhan ala sehat islami. Kami menyediakan berbagai produk herbal berkualitas yang sudah teruji manfaatnya, sekaligus membuka kesempatan bagi Anda untuk tumbuh bersama sebagai mitra.',
        'training' => 'Pelatihan penyembuhan ala sehat islami: belajar pola hidup sehat sesuai tuntunan Islam, pengenalan herbal thibbun nabawi, dan praktik terapi yang bisa diterapkan untuk keluarga maupun sebagai bekal usaha.',
        'address' => 'Jalan Raya Tajur No. 69 B, Bogor',
        'phone' => '081219266649',
        'whatsapp' => '081219266649',
        'email' => 'support@mpk-herbattaubah.com',
        'instagram' => '',
        'facebook' => '',
        'maps_embed' => '',
        'bonus_min_personal' => '1000000',
        'bonus_pct' => '10',
        'voucher_min_group' => '5000000',
        'voucher_amount' => '1000000',
        'royalty_pct' => '5',
        'group_depth' => '1',
    ];

    public static function get(string $key, ?string $default = null): string
    {
        if (static::$cache === null) {
            try {
                static::$cache = static::query()->pluck('value', 'key')->map(fn ($v) => (string) $v)->all();
            } catch (Throwable) {
                static::$cache = [];
            }
        }

        return static::$cache[$key] ?? $default ?? (self::DEFAULTS[$key] ?? '');
    }

    public static function put(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value ?? '']);
        static::$cache = null;
    }

    public static function flushCache(): void
    {
        static::$cache = null;
    }
}
