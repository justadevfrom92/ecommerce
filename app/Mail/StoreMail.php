<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

/**
 * Every email the store sends: a plain-text body (escaped and line-broken in
 * the HTML version), an optional button, and tracking headers the log reads.
 */
class StoreMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $mailSubject,
        public string $body,
        public ?string $buttonText = null,
        public ?string $buttonUrl = null,
        public ?string $templateKey = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mailSubject,
            replyTo: [new Address(setting('store_email'), setting('store_name'))],
        );
    }

    public function headers(): Headers
    {
        $text = array_filter([
            'X-Store-Template' => $this->templateKey,
        ]);

        return new Headers(text: $text);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.store', text: 'emails.store-text');
    }
}
