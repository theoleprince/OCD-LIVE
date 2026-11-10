<?php

namespace App\Providers;

use App\Enums\MarketPermission;
use App\Models\User;
use App\Payments\PaymentManager;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentManager::class);
    }

    public function boot(): void
    {
        Carbon::setLocale('fr');
        CarbonImmutable::setLocale('fr');

        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        Paginator::defaultView('pagination::tailwind');
        Paginator::defaultSimpleView('pagination::simple-tailwind');

        Gate::define('market', fn (User $user, MarketPermission $permission) => $user->is_active
            && in_array($permission, $user->role->marketPermissions(), true));
    }
}
