<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;

class ServicePolicy
{
    public function manageServices(User $user): bool
    {
        return $user->hasPermission('master.manage_services');
    }
}