<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\DataTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = Order::query()->with('user')->withCount('items');

        if (array_key_exists((string) $request->query('status'), Order::STATUSES)) {
            $query->where('status', $request->query('status'));
        }

        if ($from = $request->date('from')) {
            $query->where('created_at', '>=', $from->startOfDay());
        }

        if ($to = $request->date('to')) {
            $query->where('created_at', '<=', $to->endOfDay());
        }

        $orders = DataTable::paginate($query, $request,
            searchable: ['number', 'email', 'shipping_name'],
            sortable: ['number', 'created_at', 'total', 'status', 'shipping_name'],
            defaultSort: 'created_at',
        );

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', ['order' => $order->load('items.product', 'user')]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Order::STATUSES))],
            'admin_note' => ['nullable', 'string', 'max:5000'],
        ]);

        $order->update(['admin_note' => $data['admin_note']]);
        $order->transitionTo($data['status']);

        return back()->with('status', "Order {$order->number} updated.");
    }
}
