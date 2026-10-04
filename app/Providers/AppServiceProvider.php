<?php

namespace App\Providers;

use App\Models\Reminder;
use App\Models\Service;
use App\Models\ServiceHistory;
use App\Models\Sparepart;
use App\Models\User;
use App\Policies\ReminderPolicy;
use App\Policies\ServiceHistoryPolicy;
use App\Policies\ServicePolicy;
use App\Policies\SparepartPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Sparepart::class, SparepartPolicy::class);
        Gate::policy(Reminder::class, ReminderPolicy::class);
        Gate::policy(ServiceHistory::class, ServiceHistoryPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Service::class, ServicePolicy::class);

        $this->app->rebinding('request', function ($app): void {
            if ($app->resolved('auth')) {
                $app['auth']->guard('sanctum')->forgetUser();
            }
        });
    }
}
