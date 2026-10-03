<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('kasir.view_checklist')
            || $user->hasPermission('kasir.process_payment')
            || $user->hasPermission('kasir.print_invoice');
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->hasPermission('kasir.view_checklist')
            || $user->hasPermission('kasir.process_payment')
            || $user->hasPermission('kasir.print_invoice');
    }

    public function processPayment(User $user, Invoice $invoice): bool
    {
        return $user->hasPermission('kasir.process_payment');
    }

    public function print(User $user, Invoice $invoice): bool
    {
        return $user->hasPermission('kasir.print_invoice');
    }
}
