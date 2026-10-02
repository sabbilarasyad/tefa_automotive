<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PkbService extends Model
{
    protected $table = 'pkb_services';

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'documentation_required' => 'boolean',
            'is_done' => 'boolean',
        ];
    }

    public function pkb(): BelongsTo
    {
        return $this->belongsTo(Pkb::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function documentations(): HasMany
    {
        return $this->hasMany(Documentation::class);
    }
}
