<?php

namespace App\Providers;

use App\Models\Reminder;
use App\Models\ServiceHistory;
use App\Models\Sparepart;
use App\Policies\ReminderPolicy;
use App\Policies\ServiceHistoryPolicy;
use App\Policies\SparepartPolicy;
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

        $this->app->rebinding('request', function ($app): void {
            if ($app->resolved('auth')) {
                $app['auth']->guard('sanctum')->forgetUser();
            }
        });
    }
}
