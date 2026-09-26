<?php

namespace App\Providers;

use App\Models\Department;
use App\Models\User;
use App\Services\Cart;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(Cart::class, fn ($app) => new Cart($app['session.store']));
    }

    public function boot(): void
    {
        // Every permission slug (config/store.php) is usable as a gate:
        // @can('products.manage'), ->middleware('can:orders.view'), etc.
        Gate::before(function (User $user, string $ability) {
            return $user->hasPermission($ability) ? true : null;
        });

        View::composer('components.layouts.store', function ($view) {
            $view->with('cartCount', app(Cart::class)->count());
            $view->with('navDepartments', Cache::remember('nav.departments', 600, fn () => Department::active()->orderBy('sort_order')->orderBy('name')->get(['name', 'slug'])->toArray()));
        });
    }
}
