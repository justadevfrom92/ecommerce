<?php

namespace App\Providers;

use App\Events\OrderPaid;
use App\Events\OrderStatusChanged;
use App\Listeners\SendStoreEmails;
use App\Models\Department;
use App\Models\User;
use App\Services\Cart;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
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

        // LogSentEmail (MessageSent) is auto-discovered from app/Listeners.
        Event::listen(Registered::class, [SendStoreEmails::class, 'welcome']);
        Event::listen(OrderPaid::class, [SendStoreEmails::class, 'orderPaid']);
        Event::listen(OrderStatusChanged::class, [SendStoreEmails::class, 'orderStatusChanged']);

        View::composer('components.layouts.store', function ($view) {
            $cart = app(Cart::class);
            $view->with('cartCount', $cart->count());
            $lines = $cart->count() > 0 ? $cart->lines() : collect();
            $view->with('cartLines', $lines);
            $view->with('cartTotals', $cart->totals($lines));
            $view->with('navDepartments', Cache::remember('nav.departments', 600, fn () => Department::active()->orderBy('sort_order')->orderBy('name')
                ->with(['categories' => fn ($q) => $q->where('is_active', true)->select(['id', 'department_id', 'name', 'slug'])])
                ->get(['id', 'name', 'slug'])
                ->map(fn ($d) => ['name' => $d->name, 'slug' => $d->slug, 'categories' => $d->categories->map->only(['name', 'slug'])->all()])
                ->all()));
        });
    }
}
