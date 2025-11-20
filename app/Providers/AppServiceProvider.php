<?php

namespace App\Providers;

use App\Support\AuditLogger;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
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
        Gate::define('access-admin', function ($user) {
            return $user->isAdmin();
        });

        Event::listen(Login::class, function (Login $event) {
            $email = $event->user instanceof \App\Models\User ? $event->user->email : null;

            app(AuditLogger::class)->log(
                action: 'auth.login',
                title: 'User logged in',
                description: 'Successful authentication',
                model: $event->user,
                changes: array_filter([
                    'email' => $email,
                ]),
                actor: $event->user,
            );
        });
    }
}
