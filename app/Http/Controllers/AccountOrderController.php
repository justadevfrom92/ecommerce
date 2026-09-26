<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\DataTable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountOrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = $request->user()->orders()->withCount('items')->getQuery();

        if (array_key_exists($request->query('status'), Order::STATUSES)) {
            $query->where('status', $request->query('status'));
        }

        $orders = DataTable::paginate($query, $request,
            searchable: ['number'],
            sortable: ['number', 'created_at', 'total', 'status'],
            defaultSort: 'created_at',
        );

        return view('account.orders.index', compact('orders'));
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless((int) $order->user_id === $request->user()->id, 404);

        return view('account.orders.show', ['order' => $order->load('items.product')]);
    }
}
