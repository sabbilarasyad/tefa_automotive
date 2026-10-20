<?php

namespace App\Providers;

use App\Models\ContactMessage;
use App\Policies\ContactMessagePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(ContactMessage::class, ContactMessagePolicy::class);
    }
}
