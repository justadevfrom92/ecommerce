<?php

namespace App\Services;

use App\Mail\StoreMail;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class Emailer
{
    /**
     * Sends a built-in template. $vars fill its {{placeholders}}; store_name is always available.
     */
    public function sendTemplate(string $to, string $key, array $vars = [], ?string $buttonUrl = null): bool
    {
        $mail = $this->buildTemplate($key, $vars, $buttonUrl);

        return $this->send($to, $mail);
    }

    public function buildTemplate(string $key, array $vars = [], ?string $buttonUrl = null): StoreMail
    {
        $template = EmailTemplate::firstWhere('key', $key)
            ?? new EmailTemplate(['key' => $key, ...array_intersect_key(EmailTemplate::DEFAULTS[$key], array_flip(['subject', 'body', 'button_text']))]);

        $vars = ['store_name' => setting('store_name'), ...$vars];

        return new StoreMail(
            mailSubject: self::fill($template->subject, $vars),
            body: self::fill($template->body, $vars),
            buttonText: $buttonUrl ? $template->button_text : null,
            buttonUrl: $buttonUrl,
            templateKey: $key,
            unsubscribeUrl: $vars['unsubscribe_url'] ?? null,
        );
    }

    /** Sends and records failures in the email log (successes are logged by LogSentEmail). */
    public function send(string $to, StoreMail $mail): bool
    {
        try {
            Mail::to($to)->send($mail);

            return true;
        } catch (Throwable $e) {
            Log::error('Email failed', ['to' => $to, 'subject' => $mail->mailSubject, 'error' => $e->getMessage()]);

            EmailLog::create([
                'to' => $to,
                'subject' => $mail->mailSubject,
                'template_key' => $mail->templateKey,
                'campaign_id' => $mail->campaignId,
                'status' => 'failed',
                'error' => mb_substr($e->getMessage(), 0, 2000),
                'status_at' => now(),
            ]);

            return false;
        }
    }

    /** Replaces {{ key }} placeholders; unknown ones are left as-is so typos are visible. */
    public static function fill(string $text, array $vars): string
    {
        return preg_replace_callback('/\{\{\s*([a-z0-9_]+)\s*\}\}/i', fn ($m) => array_key_exists($m[1], $vars) ? (string) $vars[$m[1]] : $m[0], $text);
    }
}
