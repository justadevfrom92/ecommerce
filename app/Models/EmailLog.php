<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['to', 'subject', 'template_key', 'campaign_id', 'message_id', 'status', 'error', 'status_at'])]
class EmailLog extends Model
{
    public const STATUSES = [
        'sent' => 'text-bg-light border',
        'delivered' => 'text-bg-success',
        'opened' => 'text-bg-info',
        'clicked' => 'text-bg-primary',
        'failed' => 'text-bg-danger',
        'bounced' => 'text-bg-danger',
        'complained' => 'text-bg-warning',
    ];

    protected function casts(): array
    {
        return ['status_at' => 'datetime'];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function badge(): string
    {
        return self::STATUSES[$this->status] ?? 'text-bg-light';
    }
}
