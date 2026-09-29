<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'qty_in' => 'decimal:3',
            'qty_out' => 'decimal:3',
            'stock_before' => 'decimal:3',
            'stock_after' => 'decimal:3',
            'created_at' => 'datetime',
        ];
    }

    public function sparepart(): BelongsTo
    {
        return $this->belongsTo(Sparepart::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}