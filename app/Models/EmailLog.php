<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['to', 'subject', 'template_key', 'campaign_id', 'message_id', 'status', 'error', 'status_at'])]
class EmailLog extends Model
{
    public const STATUSES = [
        'sent' => 'pill pill-neutral',
        'delivered' => 'pill pill-success',
        'opened' => 'pill pill-info',
        'clicked' => 'pill pill-primary',
        'failed' => 'pill pill-danger',
        'bounced' => 'pill pill-danger',
        'complained' => 'pill pill-warning',
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
        return self::STATUSES[$this->status] ?? 'pill pill-neutral';
    }
}
