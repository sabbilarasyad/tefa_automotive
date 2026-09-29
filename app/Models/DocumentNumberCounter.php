<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentNumberCounter extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'last_number' => 'integer',
        ];
    }
}