<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Documentation extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'uploaded_at' => 'datetime',
        ];
    }

    public function pkb(): BelongsTo
    {
        return $this->belongsTo(Pkb::class);
    }

    public function pkbService(): BelongsTo
    {
        return $this->belongsTo(PkbService::class);
    }

    public function pkbSparepart(): BelongsTo
    {
        return $this->belongsTo(PkbSparepart::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}