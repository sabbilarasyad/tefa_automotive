<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'issued_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function pkb(): BelongsTo
    {
        return $this->belongsTo(Pkb::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}