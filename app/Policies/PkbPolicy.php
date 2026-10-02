<?php

namespace App\Policies;

use App\Models\Pkb;
use App\Models\User;

class PkbPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('pkb.view_all')
            || $user->hasPermission('pkb.view_own')
            || $user->hasPermission('pkb.view_waiting_payment');
    }

    public function view(User $user, Pkb $pkb): bool
    {
        return $user->hasPermission('pkb.view_all')
            || ($user->hasPermission('pkb.view_own') && $this->viewOwn($user, $pkb))
            || ($user->hasPermission('pkb.view_waiting_payment') && $pkb->status === 'WAITING_PAYMENT');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('pkb.create');
    }

    public function edit(User $user, Pkb $pkb): bool
    {
        return $user->hasPermission('pkb.edit')
            && in_array($pkb->status, ['WAITING_PARTS', 'WAITING_MECHANIC'], true);
    }

    public function assignMechanic(User $user, Pkb $pkb): bool
    {
        return $user->hasPermission('pkb.assign_mechanic');
    }

    public function cancel(User $user, Pkb $pkb): bool
    {
        return $user->hasPermission('pkb.cancel')
            && in_array($pkb->status, ['WAITING_PARTS', 'WAITING_MECHANIC'], true);
    }

    public function forceCancel(User $user, Pkb $pkb): bool
    {
        return $user->hasPermission('pkb.force_cancel') && $pkb->status !== 'CANCELLED';
    }

    public function print(User $user, Pkb $pkb): bool
    {
        return $user->hasPermission('pkb.print') && $pkb->mechanic_id !== null;
    }

    public function viewOwn(User $user, Pkb $pkb): bool
    {
        return (int) $pkb->mechanic_id === (int) $user->id;
    }
}
