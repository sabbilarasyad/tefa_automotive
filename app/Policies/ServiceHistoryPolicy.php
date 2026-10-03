<?php

namespace App\Policies;

use App\Models\ServiceHistory;
use App\Models\User;

class ServiceHistoryPolicy
{
    public function view(User $user): bool
    {
        return $user->hasRole('SUPER_ADMIN', 'HEAD_TEFA', 'ADMIN_CS', 'MEKANIK', 'KASIR');
    }
}