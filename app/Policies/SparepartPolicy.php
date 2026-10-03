<?php

namespace App\Policies;

use App\Models\Sparepart;
use App\Models\User;

class SparepartPolicy
{
    public function manageSpareparts(User $user): bool
    {
        return $user->hasPermission('master.manage_spareparts');
    }

    public function adjustStock(User $user): bool
    {
        return $user->hasPermission('master.adjust_stock');
    }
}