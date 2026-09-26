<?php

namespace App\Jobs;

use App\Mail\StoreMail;
use App\Models\Campaign;
use App\Models\Subscriber;
use App\Services\Emailer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/** Sends a campaign to every current subscriber. Run a queue worker: php artisan queue:work */
class SendCampaign implements ShouldQueue
{
    use Queueable;

    public int $timeout = 3600;

    public int $tries = 1;

    public function __construct(public Campaign $campaign) {}

    public function handle(Emailer $emailer): void
    {
        $campaign = $this->campaign;

        Subscriber::subscribed()->orderBy('id')->chunkById(200, function ($subscribers) use ($campaign, $emailer) {
            foreach ($subscribers as $subscriber) {
                $unsubscribe = $subscriber->unsubscribeUrl();

                $ok = $emailer->send($subscriber->email, new StoreMail(
                    mailSubject: $campaign->subject,
                    body: Emailer::fill($campaign->body, ['store_name' => setting('store_name'), 'name' => $subscriber->name ?: 'there', 'unsubscribe_url' => $unsubscribe]),
                    buttonText: $campaign->button_text,
                    buttonUrl: $campaign->button_url,
                    campaignId: $campaign->id,
                    unsubscribeUrl: $unsubscribe,
                ));

                $campaign->increment($ok ? 'sent_count' : 'failed_count');
            }
        });

        $campaign->update(['status' => 'sent', 'sent_at' => now()]);
    }

    public function failed(): void
    {
        // Leave it visible as sent-with-errors rather than stuck on "sending".
        $this->campaign->update(['status' => 'sent', 'sent_at' => now()]);
    }
}
