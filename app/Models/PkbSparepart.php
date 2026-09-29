<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PkbSparepart extends Model
{
    protected $table = 'pkb_spareparts';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'qty_planned' => 'decimal:3',
            'qty_used' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'documentation_required' => 'boolean',
        ];
    }

    public function pkb(): BelongsTo
    {
        return $this->belongsTo(Pkb::class);
    }

    public function sparepart(): BelongsTo
    {
        return $this->belongsTo(Sparepart::class);
    }
}