<?php

namespace App\Policies;

use App\Models\ContactMessage;
use App\Models\User;

class ContactMessagePolicy
{
    public function manageContactMessages(User $user): bool
    {
        return $user->hasPermission('contact_message.manage');
    }

    public function delete(User $user): bool
    {
        return $user->hasRole('SUPER_ADMIN');
    }
}