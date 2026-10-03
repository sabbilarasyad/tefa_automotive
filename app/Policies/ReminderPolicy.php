<?php

namespace App\Policies;

use App\Models\Reminder;
use App\Models\User;

class ReminderPolicy
{
    public function manageReminders(User $user): bool
    {
        return $user->hasPermission('reminder.manage');
    }
}