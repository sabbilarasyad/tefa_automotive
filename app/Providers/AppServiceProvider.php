<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use App\Models\Reminder;
use App\Models\Report;
use App\Models\Service;
use App\Models\ServiceHistory;
use App\Models\Sparepart;
use App\Models\User;
use App\Policies\ReminderPolicy;
use App\Policies\ReportPolicy;
use App\Policies\ServiceHistoryPolicy;
use App\Policies\ServicePolicy;
use App\Policies\SparepartPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
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
        DB::prohibitDestructiveCommands(
    app()->isProduction() || config('database.connections.mysql.database') === 'tefa_automotive'
        );
        Gate::policy(Sparepart::class, SparepartPolicy::class);
        Gate::policy(Reminder::class, ReminderPolicy::class);
        Gate::policy(ServiceHistory::class, ServiceHistoryPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Service::class, ServicePolicy::class);
        Gate::policy(Report::class, ReportPolicy::class);

        View::composer('layouts.public', function ($view) {
            $view->with('footerServices', Service::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->limit(4)
                ->get());
        });

                /** @var \Illuminate\Foundation\Application $container */
        $container = $this->app;

        $container->rebinding('request', function ($app): void {
            if ($app->resolved('auth')) {
                $app['auth']->guard('sanctum')->forgetUser();
            }
        });
    }
}
