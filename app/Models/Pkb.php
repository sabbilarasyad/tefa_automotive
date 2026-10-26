<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pkb extends Model
{
    use HasFactory;

    protected $table = 'pkb';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'service_date' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'estimated_total' => 'decimal:2',
            'final_total' => 'decimal:2',
        ];
    }

    public function getMechanicDisplayNameAttribute(): ?string
    {
        return $this->mechanic?->name ?? $this->mechanic_name;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function mechanic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mechanic_id');
    }

    public function services(): HasMany
    {
        return $this->hasMany(PkbService::class);
    }

    public function spareparts(): HasMany
    {
        return $this->hasMany(PkbSparepart::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(PkbStatusHistory::class);
    }

    public function documentations(): HasMany
    {
        return $this->hasMany(Documentation::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }
}
