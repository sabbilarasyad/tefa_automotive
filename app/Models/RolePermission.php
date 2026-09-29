<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    protected $table = 'role_permissions';

    protected $primaryKey = null;

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];
}