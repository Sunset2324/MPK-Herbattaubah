<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    protected $fillable = [
        'member_code', 'name', 'phone', 'email', 'address', 'sponsor_id', 'status', 'joined_at', 'notes',
    ];

    protected function casts(): array
    {
        return ['joined_at' => 'date'];
    }

    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'sponsor_id');
    }

    public function downlines(): HasMany
    {
        return $this->hasMany(Member::class, 'sponsor_id')->orderBy('name');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'aktif';
    }

    /** Kode member baru: MPK + 7 digit berurutan. */
    public static function nextCode(): string
    {
        $max = 0;
        foreach (static::where('member_code', 'like', 'MPK%')->pluck('member_code') as $code) {
            $n = (int) preg_replace('/\D/', '', $code);
            if ($n < 10000000 && $n > $max) {
                $max = $n;
            }
        }

        return sprintf('MPK%07d', $max + 1);
    }

    /** Cegah sponsor melingkar: true bila $candidateId adalah member ini sendiri atau downline-nya. */
    public static function wouldCreateCycle(int $memberId, int $candidateId): bool
    {
        $seen = [];
        $cur = $candidateId;
        while ($cur && ! isset($seen[$cur])) {
            if ($cur === $memberId) {
                return true;
            }
            $seen[$cur] = true;
            $cur = (int) static::whereKey($cur)->value('sponsor_id');
        }

        return false;
    }
}
