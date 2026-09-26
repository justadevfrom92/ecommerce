<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['source', 'from_email', 'from_name', 'subject', 'body', 'message_id', 'user_id', 'read_at', 'replied_at'])]
class InboundMessage extends Model
{
    protected function casts(): array
    {
        return ['read_at' => 'datetime', 'replied_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(InboundMessageReply::class)->oldest();
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }
}
