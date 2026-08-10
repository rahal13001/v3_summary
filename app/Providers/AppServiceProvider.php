<?php

namespace App\Providers;

use App\Notifications\Auth\ResetPassword as SynchronousResetPassword;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Filament\Auth\Notifications\ResetPassword;
use Filament\Pages\BasePage as Page;
use Filament\Resources\Resource;
use Filament\Support\Facades\FilamentColor;
use Filament\Widgets\Widget;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            ResetPassword::class,
            fn ($app, array $parameters) => new SynchronousResetPassword($parameters['token']),
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request): Limit {
            return Limit::perMinute(5)->by(Str::lower((string) $request->input('email')).'|'.$request->ip());
        });

        FilamentShield::prohibitDestructiveCommands($this->app->isProduction());

        FilamentShield::buildPermissionKeyUsing(
            fn (string $entity, ?string $affix, string $subject, string $case, string $separator): string => match (true) {
                is_subclass_of($entity, Resource::class) => Str::of($affix)
                    ->snake()
                    ->append('_')
                    ->append(
                        Str::of($entity)
                            ->afterLast('\\')
                            ->beforeLast('Resource')
                            ->replace('\\', '')
                            ->snake()
                            ->replace('_', '::'),
                    )
                    ->toString(),
                is_subclass_of($entity, Page::class) => Str::of('page_')->append(class_basename($entity))->toString(),
                is_subclass_of($entity, Widget::class) => Str::of('widget_')->append(class_basename($entity))->toString(),
                default => $subject,
            },
        );

        FilamentColor::register([
            'indigo' => '#4338CA',
            'purple' => '#A855F7',
            'blue' => '#3B82F6',
        ]);
    }
}
