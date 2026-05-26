<?php

namespace App\Providers;

use App\Notifications\Auth\ResetPassword as SynchronousResetPassword;
use Filament\Notifications\Auth\ResetPassword as QueuedFilamentResetPassword;
use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentColor;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            QueuedFilamentResetPassword::class,
            fn ($app, array $parameters) => new SynchronousResetPassword($parameters['token']),
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FilamentColor::register([
            'indigo' => '#4338CA',
            'purple' => '#A855F7',
            'blue' => '#3B82F6',
        ]);
    }
}
