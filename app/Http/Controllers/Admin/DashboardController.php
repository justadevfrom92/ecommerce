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

        $since = now()->subDays(30);
        $sold = Order::whereIn('status', self::SOLD)->where('created_at', '>=', $since);
        $revenue = (float) (clone $sold)->sum('total');
        $orderCount = (clone $sold)->count();

        return view('admin.dashboard', [
            'stats' => [
                ['label' => 'Revenue (30 days)', 'value' => money($revenue), 'icon' => 'currency-dollar', 'color' => 'success'],
                ['label' => 'Orders (30 days)', 'value' => number_format($orderCount), 'icon' => 'receipt', 'color' => 'primary'],
                ['label' => 'Average order', 'value' => money($orderCount ? $revenue / $orderCount : 0), 'icon' => 'graph-up', 'color' => 'info'],
                ['label' => 'New customers (30 days)', 'value' => number_format(User::where('created_at', '>=', $since)->count()), 'icon' => 'person-plus', 'color' => 'warning'],
            ],
            'toFulfil' => Order::whereIn('status', ['paid', 'processing'])->count(),
            'awaitingPayment' => Order::where('status', 'pending_payment')->count(),
            'recentOrders' => Order::latest()->limit(8)->get(),
            'topProducts' => OrderItem::query()
                ->selectRaw('name, SUM(quantity) as units, SUM(line_total) as revenue')
                ->whereHas('order', fn ($q) => $q->whereIn('status', self::SOLD)->where('created_at', '>=', $since))
                ->groupBy('name')->orderByDesc('units')->limit(5)->get(),
            'lowStock' => Product::with('category')->where('stock', '<=', 5)->orderBy('stock')->limit(6)->get(),
        ]);
    }
}
