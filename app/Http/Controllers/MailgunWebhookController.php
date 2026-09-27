<?php

namespace App\Http\Controllers;

use App\Models\EmailLog;
use App\Models\InboundMessage;
use App\Models\User;
use App\Services\Mailgun;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MailgunWebhookController extends Controller
{
    /** Mailgun event name => our log status. */
    private const EVENT_STATUS = [
        'delivered' => 'delivered',
        'opened' => 'opened',
        'clicked' => 'clicked',
        'failed' => 'failed',
        'complained' => 'complained',
    ];

    /** Delivery / engagement events for mail we sent. */
    public function events(Request $request, Mailgun $mailgun): Response
    {
        $sig = $request->input('signature', []);

        if (! $mailgun->verify($sig['timestamp'] ?? null, $sig['token'] ?? null, $sig['signature'] ?? null)) {
            return response('Invalid signature', 406); // 406 tells Mailgun not to retry
        }

        $data = $request->input('event-data', []);
        $event = $data['event'] ?? null;
        $messageId = trim((string) ($data['message']['headers']['message-id'] ?? ''), '<>');

        if ($messageId !== '' && isset(self::EVENT_STATUS[$event])) {
            $status = $event === 'failed' && ($data['severity'] ?? null) === 'permanent' ? 'bounced' : self::EVENT_STATUS[$event];
            $query = EmailLog::where('message_id', $messageId);

            // Don't let a late "delivered" overwrite "opened"/"clicked".
            if ($status === 'delivered') {
                $query->whereIn('status', ['sent', 'failed']);
            }

            $query->update([
                'status' => $status,
                'error' => in_array($status, ['failed', 'bounced'], true) ? mb_substr((string) ($data['delivery-status']['message'] ?? $data['delivery-status']['description'] ?? ''), 0, 2000) : null,
                'status_at' => now(),
            ]);
        }

        return response('OK');
    }

    /** Incoming mail forwarded by a Mailgun Route -> admin inbox. */
    public function inbound(Request $request, Mailgun $mailgun): Response
    {
        if (! $mailgun->verify($request->input('timestamp'), $request->input('token'), $request->input('signature'))) {
            return response('Invalid signature', 406);
        }

        [$fromName, $fromEmail] = $this->parseFrom((string) $request->input('from', $request->input('sender', '')));

        if ($fromEmail === '') {
            return response('Missing sender', 406);
        }

        InboundMessage::create([
            'source' => 'email',
            'from_email' => $fromEmail,
            'from_name' => $fromName,
            'subject' => mb_substr((string) $request->input('subject', ''), 0, 255) ?: null,
            'body' => (string) ($request->input('stripped-text') ?: $request->input('body-plain', '')),
            'message_id' => trim((string) $request->input('Message-Id', ''), '<>') ?: null,
            'user_id' => User::where('email', $fromEmail)->value('id'),
        ]);

        return response('OK');
    }

    /** "Jane Doe <jane@example.com>" -> ["Jane Doe", "jane@example.com"] */
    private function parseFrom(string $from): array
    {
        if (preg_match('/^\s*"?([^"<]*)"?\s*<([^>]+)>/', $from, $m)) {
            return [trim($m[1]) ?: null, strtolower(trim($m[2]))];
        }

        $email = strtolower(trim($from));

        return [null, filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : ''];
    }
}
