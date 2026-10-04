<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function manageUsers(User $user): bool
    {
        return $user->hasPermission('user.manage');
    }

    public function resetPassword(User $user): bool
    {
        return $user->hasPermission('user.reset_password');
    }
}