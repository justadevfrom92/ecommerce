<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\View\View;

/** Compact sales report: revenue per day for the last 30 days. */
class SalesController extends Controller
{
    /** Orders that count as a sale. */
    private const SOLD = ['paid', 'processing', 'shipped', 'delivered'];

    public function __invoke(): View
    {
        $since = now()->subDays(29)->startOfDay();

        $orders = Order::whereIn('status', self::SOLD)->where('created_at', '>=', $since)->get(['created_at', 'total']);
        $byDay = $orders->groupBy(fn ($o) => $o->created_at->toDateString())->map(fn ($g) => round($g->sum('total'), 2));

        $series = collect(range(0, 29))->map(function ($i) use ($since, $byDay) {
            $day = $since->copy()->addDays($i);

            return ['date' => $day, 'value' => (float) ($byDay[$day->toDateString()] ?? 0)];
        });

        $revenue = $series->sum('value');

        return view('admin.sales', [
            'series' => $series,
            'revenue' => $revenue,
            'orderCount' => $orders->count(),
            'average' => $orders->count() ? $revenue / $orders->count() : 0,
        ]);
    }
}
