<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceHistory extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'service_date' => 'datetime',
            'km' => 'integer',
            'total_amount' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function pkb(): BelongsTo
    {
        return $this->belongsTo(Pkb::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}