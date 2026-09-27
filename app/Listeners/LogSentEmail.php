<?php

namespace App\Listeners;

use App\Models\EmailLog;
use Illuminate\Mail\Events\MessageSent;

/** Writes every successfully handed-off email to the outgoing log. */
class LogSentEmail
{
    public function handle(MessageSent $event): void
    {
        $message = $event->message;
        $headers = $message->getHeaders();

        $to = collect($message->getTo())->map->getAddress()->join(', ');
        $messageId = trim((string) $event->sent->getMessageId(), '<>');

        EmailLog::create([
            'to' => $to,
            'subject' => (string) $message->getSubject(),
            'template_key' => $headers->get('X-Store-Template')?->getBodyAsString(),
            'message_id' => $messageId ?: null,
            'status' => 'sent',
            'status_at' => now(),
        ]);
    }
}
