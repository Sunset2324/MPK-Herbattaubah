<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Purchase extends Model
{
    protected $fillable = ['member_id', 'purchase_date', 'amount', 'note'];

    protected function casts(): array
    {
        return ['purchase_date' => 'date', 'amount' => 'integer'];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
