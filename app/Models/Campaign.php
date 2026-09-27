<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['subject', 'body', 'button_text', 'button_url', 'status', 'recipients_count', 'sent_count', 'failed_count', 'created_by', 'sent_at'])]
class Campaign extends Model
{
    public const STATUSES = [
        'draft' => 'pill pill-neutral',
        'sending' => 'pill pill-warning',
        'sent' => 'pill pill-success',
    ];

    protected function casts(): array
    {
        return ['sent_at' => 'datetime'];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(EmailLog::class);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }
}
