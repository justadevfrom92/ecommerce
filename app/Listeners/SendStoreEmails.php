<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Events\OrderStatusChanged;
use App\Models\Order;
use App\Models\User;
use App\Services\Emailer;
use Illuminate\Auth\Events\Registered;

/** Sends the built-in template emails in response to store events. */
class SendStoreEmails
{
    /** Status changes the customer is told about. */
    private const NOTIFY_STATUSES = ['shipped', 'delivered', 'cancelled', 'refunded'];

    public function __construct(private Emailer $emailer) {}

    public function welcome(Registered $event): void
    {
        /** @var User $user */
        $user = $event->user;

        $this->emailer->sendTemplate($user->email, 'welcome', ['name' => $user->name, 'shop_url' => route('home')], route('home'));
    }

    public function orderPaid(OrderPaid $event): void
    {
        $order = $event->order->loadMissing('items');

        $this->emailer->sendTemplate($order->email, 'order_confirmation', [
            ...$this->orderVars($order),
            'order_items' => $order->items->map(fn ($i) => "{$i->quantity} × {$i->name} — ".money($i->line_total))->join("\n"),
        ], route('account.orders.show', $order));
    }

    public function orderStatusChanged(OrderStatusChanged $event): void
    {
        if (! in_array($event->order->status, self::NOTIFY_STATUSES, true)) {
            return;
        }

        $this->emailer->sendTemplate($event->order->email, 'order_status', $this->orderVars($event->order), route('account.orders.show', $event->order));
    }

    private function orderVars(Order $order): array
    {
        return [
            'name' => $order->shipping_name,
            'order_number' => $order->number,
            'order_total' => money($order->total),
            'order_status' => $order->statusLabel(),
            'order_url' => route('account.orders.show', $order),
        ];
    }
}
