<?php

namespace App\Http\Controllers\Admin\Emails;

use App\Http\Controllers\Controller;
use App\Jobs\SendCampaign;
use App\Mail\StoreMail;
use App\Models\Campaign;
use App\Models\Subscriber;
use App\Services\Emailer;
use App\Support\DataTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CampaignController extends Controller
{
    public function index(Request $request): View
    {
        $campaigns = DataTable::paginate(Campaign::query()->with('author'), $request,
            searchable: ['subject'],
            sortable: ['subject', 'status', 'sent_at', 'created_at'],
            defaultSort: 'created_at',
        );

        return view('admin.emails.campaigns.index', compact('campaigns'));
    }

    public function create(): View
    {
        return view('admin.emails.campaigns.form', ['campaign' => new Campaign, 'subscriberCount' => Subscriber::subscribed()->count()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $campaign = Campaign::create([...$this->validated($request), 'status' => 'draft', 'created_by' => $request->user()->id]);

        return redirect()->route('admin.emails.campaigns.edit', $campaign)->with('status', 'Draft saved.');
    }

    public function edit(Campaign $campaign): View
    {
        $preview = $this->mail($campaign, 'there', route('home'));

        return view('admin.emails.campaigns.form', [
            'campaign' => $campaign,
            'subscriberCount' => Subscriber::subscribed()->count(),
            'preview' => ['subject' => $preview->mailSubject, 'html' => $preview->render()],
        ]);
    }

    public function update(Request $request, Campaign $campaign): RedirectResponse
    {
        abort_unless($campaign->isDraft(), 403, 'Sent campaigns can\'t be edited.');
        $campaign->update($this->validated($request));

        return back()->with('status', 'Draft saved.');
    }

    public function destroy(Campaign $campaign): RedirectResponse
    {
        abort_if($campaign->status === 'sending', 403, 'This campaign is sending right now.');
        $campaign->delete();

        return redirect()->route('admin.emails.campaigns.index')->with('status', 'Campaign deleted.');
    }

    public function test(Request $request, Campaign $campaign, Emailer $emailer): RedirectResponse
    {
        $mail = $this->mail($campaign, $request->user()->name, route('home'));
        $mail->mailSubject = '[TEST] '.$mail->mailSubject;
        $ok = $emailer->send($request->user()->email, $mail);

        return back()->with($ok ? 'status' : 'error', $ok ? "Test sent to {$request->user()->email}." : 'The test email could not be sent. Check the outgoing log.');
    }

    public function send(Campaign $campaign): RedirectResponse
    {
        $count = Subscriber::subscribed()->count();

        if ($count === 0) {
            return back()->with('error', 'There are no subscribers to send to.');
        }

        // Flip draft -> sending atomically so a double click can't send twice.
        $claimed = DB::table('campaigns')->where('id', $campaign->id)->where('status', 'draft')
            ->update(['status' => 'sending', 'recipients_count' => $count, 'updated_at' => now()]);

        if (! $claimed) {
            return back()->with('error', 'This campaign has already been sent.');
        }

        SendCampaign::dispatch($campaign->fresh());

        return redirect()->route('admin.emails.campaigns.index')->with('status', "Sending “{$campaign->subject}” to {$count} subscribers.");
    }

    private function mail(Campaign $campaign, string $name, string $unsubscribe): StoreMail
    {
        return new StoreMail(
            mailSubject: $campaign->subject,
            body: Emailer::fill($campaign->body, ['store_name' => setting('store_name'), 'name' => $name, 'unsubscribe_url' => $unsubscribe]),
            buttonText: $campaign->button_text,
            buttonUrl: $campaign->button_url,
            campaignId: $campaign->id,
            unsubscribeUrl: $unsubscribe,
        );
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:50000'],
            'button_text' => ['nullable', 'required_with:button_url', 'string', 'max:60'],
            'button_url' => ['nullable', 'required_with:button_text', 'url:http,https', 'max:2000'],
        ]);
    }
}
