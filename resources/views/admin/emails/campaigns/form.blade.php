@php($editing = $campaign->exists)
@php($locked = $editing && ! $campaign->isDraft())
<x-layouts.admin :title="$editing ? $campaign->subject : 'New campaign'">
    @if ($editing && $campaign->isDraft())
        <x-slot:actions>
            <form method="POST" action="{{ route('admin.emails.campaigns.test', $campaign) }}">
                @csrf
                <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-send me-1"></i>Send me a test</button>
            </form>
            <form method="POST" action="{{ route('admin.emails.campaigns.send', $campaign) }}" data-confirm="Send this campaign to {{ $subscriberCount }} subscribers now? This can't be undone.">
                @csrf
                <button class="btn btn-sm btn-primary" @disabled($subscriberCount === 0)><i class="bi bi-megaphone me-1"></i>Send to {{ number_format($subscriberCount) }} subscribers</button>
            </form>
        </x-slot:actions>
    @endif

    @if ($locked)
        <div class="alert alert-info">{{ $campaign->status === 'sending' ? 'This campaign is being sent' : 'This campaign was sent'.($campaign->sent_at ? ' on '.$campaign->sent_at->format('M j, Y g:i A') : '') }} to {{ number_format($campaign->recipients_count) }} subscribers ({{ number_format($campaign->sent_count) }} delivered to Mailgun, {{ number_format($campaign->failed_count) }} failed). It can no longer be edited.</div>
    @endif

    <div class="row g-4">
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ $editing ? route('admin.emails.campaigns.update', $campaign) : route('admin.emails.campaigns.store') }}">
                        @csrf
                        @if ($editing) @method('PUT') @endif
                        <fieldset @disabled($locked)>
                            <x-form.input name="subject" label="Subject" :value="$campaign->subject" required />
                            <x-form.textarea name="body" label="Message" :value="$campaign->body" rows="12" required :help="'Plain text. Use {'.'{name}'.'} for the subscriber\'s name. An unsubscribe link is added automatically.'" />
                            <div class="row">
                                <x-form.input class="col-md-5" name="button_text" label="Button text" :value="$campaign->button_text" />
                                <x-form.input class="col-md-7" name="button_url" label="Button link" type="url" :value="$campaign->button_url" placeholder="https://" />
                            </div>
                            @unless ($locked)
                                <button class="btn btn-primary">Save draft</button>
                            @endunless
                            <a href="{{ route('admin.emails.campaigns.index') }}" class="btn btn-link">Back</a>
                        </fieldset>
                    </form>
                </div>
            </div>
            @unless ($editing)
                <p class="small text-body-secondary mt-2">Save the draft to preview it, send yourself a test, then send it to {{ number_format($subscriberCount) }} subscribers.</p>
            @endunless
        </div>
        @isset($preview)
            <div class="col-xl-6">@include('admin.emails._preview', $preview)</div>
        @endisset
    </div>
</x-layouts.admin>
