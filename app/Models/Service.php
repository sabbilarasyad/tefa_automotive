<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'documentation_required' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}