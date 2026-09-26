<?php

namespace App\Services;

/** https://documentation.mailgun.com/docs/mailgun/user-manual/tracking-messages/#securing-webhooks */
class Mailgun
{
    public function verify(?string $timestamp, ?string $token, ?string $signature, int $tolerance = 900): bool
    {
        $key = config('services.mailgun.webhook_signing_key');

        if (! $key || ! $timestamp || ! $token || ! $signature || abs(time() - (int) $timestamp) > $tolerance) {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $timestamp.$token, $key), $signature);
    }
}
