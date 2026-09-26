<?php

namespace App\Models;

use App\Events\OrderPaid;
use App\Events\OrderStatusChanged;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

#[Fillable([
    'number', 'user_id', 'status', 'email',
    'shipping_name', 'shipping_phone', 'shipping_line1', 'shipping_line2', 'shipping_city',
    'shipping_state', 'shipping_postal_code', 'shipping_country',
    'subtotal', 'shipping', 'tax', 'total', 'currency',
    'stripe_session_id', 'stripe_payment_intent', 'paid_at', 'customer_note', 'admin_note',
])]
class Order extends Model
{
    use HasFactory;

    public const STATUSES = [
        'pending_payment' => ['label' => 'Pending payment', 'badge' => 'text-bg-warning'],
        'paid' => ['label' => 'Paid', 'badge' => 'text-bg-info'],
        'processing' => ['label' => 'Processing', 'badge' => 'text-bg-primary'],
        'shipped' => ['label' => 'Shipped', 'badge' => 'text-bg-dark'],
        'delivered' => ['label' => 'Delivered', 'badge' => 'text-bg-success'],
        'cancelled' => ['label' => 'Cancelled', 'badge' => 'text-bg-secondary'],
        'refunded' => ['label' => 'Refunded', 'badge' => 'text-bg-danger'],
    ];

    /** Statuses whose stock has been given back. */
    private const RESTOCKED = ['cancelled', 'refunded'];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'shipping' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    public static function generateNumber(): string
    {
        do {
            $number = 'MS-'.now()->format('ymd').'-'.Str::upper(Str::random(5));
        } while (static::where('number', $number)->exists());

        return $number;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status]['label'] ?? Str::headline($this->status);
    }

    public function statusBadge(): string
    {
        return self::STATUSES[$this->status]['badge'] ?? 'text-bg-light';
    }

    public function isAwaitingPayment(): bool
    {
        return $this->status === 'pending_payment';
    }

    public function shippingAddressLines(): array
    {
        return array_values(array_filter([
            $this->shipping_name,
            $this->shipping_line1,
            $this->shipping_line2,
            trim($this->shipping_city.', '.$this->shipping_state.' '.$this->shipping_postal_code, ', '),
            $this->shipping_country,
            $this->shipping_phone,
        ]));
    }

    /** Idempotent: safe to call from both the success redirect and the webhook. */
    public function markPaid(?string $paymentIntent = null): bool
    {
        $updated = static::whereKey($this->id)->where('status', 'pending_payment')->update([
            'status' => 'paid',
            'paid_at' => now(),
            'stripe_payment_intent' => $paymentIntent,
        ]);

        if ($updated) {
            $this->refresh();
            OrderPaid::dispatch($this);
        }

        return (bool) $updated;
    }

    /** Changes status, returning stock when an order is cancelled/refunded (and taking it again if reopened). */
    public function transitionTo(string $status): void
    {
        if ($status === $this->status) {
            return;
        }

        DB::transaction(function () use ($status) {
            $wasRestocked = in_array($this->status, self::RESTOCKED, true);
            $willRestock = in_array($status, self::RESTOCKED, true);

            if ($wasRestocked !== $willRestock) {
                foreach ($this->items as $item) {
                    if ($item->product_id) {
                        $willRestock
                            ? Product::whereKey($item->product_id)->increment('stock', $item->quantity)
                            : Product::whereKey($item->product_id)->decrement('stock', min($item->quantity, (int) Product::whereKey($item->product_id)->value('stock')));
                    }
                }
            }

            $from = $this->status;
            $this->update(['status' => $status]);
            OrderStatusChanged::dispatch($this, $from);
        });
    }
}
