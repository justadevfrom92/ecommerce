<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** Orders that count as a sale. */
    private const SOLD = ['paid', 'processing', 'shipped', 'delivered'];

    public function __invoke(Request $request): View
    {
        // Everyone with admin access lands here; only dashboard.view sees the numbers.
        if ($request->user()->cannot('dashboard.view')) {
            return view('admin.dashboard', ['stats' => null]);
        }

        $since = now()->subDays(29)->startOfDay();
        $sold = Order::whereIn('status', self::SOLD);

        // Revenue per day for the last 30 days (zero-filled).
        $byDay = (clone $sold)->where('created_at', '>=', $since)->get(['created_at', 'total'])
            ->groupBy(fn ($o) => $o->created_at->toDateString())
            ->map(fn ($g) => round($g->sum('total'), 2));
        $series = collect(range(0, 29))->map(function ($i) use ($since, $byDay) {
            $day = $since->copy()->addDays($i);

            return ['date' => $day, 'value' => (float) ($byDay[$day->toDateString()] ?? 0)];
        });

        $week = now()->subDays(6)->startOfDay();
        $prevWeek = now()->subDays(13)->startOfDay();
        $ordersThisWeek = Order::where('created_at', '>=', $week)->count();
        $ordersPrevWeek = Order::whereBetween('created_at', [$prevWeek, $week])->count();
        $paidThisWeek = Order::where('created_at', '>=', $week)->whereIn('status', self::SOLD)->count();
        $customersThisWeek = User::where('created_at', '>=', $week)->count();
        $customersPrevWeek = User::whereBetween('created_at', [$prevWeek, $week])->count();

        $revenue30 = $series->sum('value');
        $orders30 = (clone $sold)->where('created_at', '>=', $since)->count();

        return view('admin.dashboard', [
            'stats' => true,
            'flags' => [
                ['num' => Order::whereIn('status', ['paid', 'processing'])->count(), 'label' => 'Awaiting fulfilment', 'noun' => 'orders', 'icon' => 'star-fill', 'bg' => '#d9fbd0', 'fg' => '#25b003', 'url' => route('admin.orders.index', ['status' => 'paid'])],
                ['num' => Order::where('status', 'pending_payment')->count(), 'label' => 'Awaiting payment', 'noun' => 'orders', 'icon' => 'pause-fill', 'bg' => '#ffefca', 'fg' => '#e5780b', 'url' => route('admin.orders.index', ['status' => 'pending_payment'])],
                ['num' => Product::where('stock', 0)->count(), 'label' => 'Out of stock', 'noun' => 'products', 'icon' => 'x-lg', 'bg' => '#ffe0db', 'fg' => '#fa3b1d', 'url' => route('admin.products.index', ['stock' => 'out'])],
            ],
            'series' => $series,
            'revenue30' => $revenue30,
            'orders30' => $orders30,
            'kpis' => [
                'orders' => ['value' => $ordersThisWeek, 'change' => self::change($ordersThisWeek, $ordersPrevWeek), 'paidPct' => $ordersThisWeek ? round($paidThisWeek / $ordersThisWeek * 100) : 0],
                'customers' => ['value' => $customersThisWeek, 'change' => self::change($customersThisWeek, $customersPrevWeek)],
                'aov' => $orders30 ? $revenue30 / $orders30 : 0,
            ],
            'recentOrders' => Order::latest()->limit(8)->get(),
            'topProducts' => OrderItem::query()
                ->selectRaw('name, SUM(quantity) as units, SUM(line_total) as revenue')
                ->whereHas('order', fn ($q) => $q->whereIn('status', self::SOLD)->where('created_at', '>=', $since))
                ->groupBy('name')->orderByDesc('units')->limit(5)->get(),
            'lowStock' => Product::with('category')->where('stock', '<=', 5)->orderBy('stock')->limit(6)->get(),
        ]);
    }

    private static function change(int $now, int $before): ?float
    {
        return $before > 0 ? round(($now - $before) / $before * 100, 1) : null;
    }
}
