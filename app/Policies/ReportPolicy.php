<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    public function view(User $user): bool
    {
        return $user->hasPermission('report.view');
    }
}