<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

#[Fillable(['email', 'name', 'status', 'source', 'subscribed_at', 'unsubscribed_at'])]
class Subscriber extends Model
{
    protected function casts(): array
    {
        return ['subscribed_at' => 'datetime', 'unsubscribed_at' => 'datetime'];
    }

    public function scopeSubscribed(Builder $query): void
    {
        $query->where('status', 'subscribed');
    }

    /** Subscribes (or re-subscribes) an address. Returns true when newly subscribed. */
    public static function subscribe(string $email, ?string $name = null, string $source = 'homepage'): bool
    {
        $subscriber = static::firstOrNew(['email' => strtolower(trim($email))]);
        $isNew = ! $subscriber->exists || $subscriber->status !== 'subscribed';

        $subscriber->fill([
            'name' => $subscriber->name ?: $name,
            'status' => 'subscribed',
            'source' => $subscriber->source ?: $source,
            'subscribed_at' => $isNew ? now() : $subscriber->subscribed_at,
            'unsubscribed_at' => null,
        ])->save();

        return $isNew;
    }

    public function unsubscribe(): void
    {
        $this->update(['status' => 'unsubscribed', 'unsubscribed_at' => now()]);
    }

    public function unsubscribeUrl(): string
    {
        return URL::signedRoute('newsletter.unsubscribe', ['subscriber' => $this->id]);
    }
}
