<x-layouts.admin title="Campaigns">
    <x-data-table :rows="$campaigns" search-placeholder="Search subject…" label="campaigns" :columns="[
        'subject' => ['label' => 'Subject', 'sortable' => true],
        'status' => ['label' => 'Status', 'sortable' => true],
        'progress' => ['label' => 'Sent / recipients', 'class' => 'text-end'],
        'sent_at' => ['label' => 'Sent at', 'sortable' => true],
        'created_at' => ['label' => 'Created', 'sortable' => true],
        'actions' => ['label' => '', 'class' => 'text-end'],
    ]">
        <x-slot:toolbar>
            <a href="{{ route('admin.emails.campaigns.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New campaign</a>
        </x-slot:toolbar>
        @foreach ($campaigns as $campaign)
            <tr>
                <td><div class="fw-semibold">{{ $campaign->subject }}</div><div class="small text-body-secondary">by {{ $campaign->author?->name ?? 'deleted user' }}</div></td>
                <td><span class="{{ \App\Models\Campaign::STATUSES[$campaign->status] ?? 'pill pill-neutral' }}">{{ $campaign->status }}</span></td>
                <td class="text-end small">
                    @if ($campaign->isDraft()) — @else
                        {{ number_format($campaign->sent_count) }} / {{ number_format($campaign->recipients_count) }}
                        @if ($campaign->failed_count)<div class="text-danger">{{ $campaign->failed_count }} failed</div>@endif
                    @endif
                </td>
                <td class="small text-body-secondary">{{ $campaign->sent_at?->format('M j, Y g:i A') ?? '—' }}</td>
                <td class="small text-body-secondary">{{ $campaign->created_at->format('M j, Y') }}</td>
                <td class="text-end text-nowrap">
                    <a href="{{ route('admin.emails.campaigns.edit', $campaign) }}" class="btn btn-sm btn-soft">{{ $campaign->isDraft() ? 'Edit' : 'View' }}</a>
                    @if ($campaign->status !== 'sending')
                        <x-delete-button :action="route('admin.emails.campaigns.destroy', $campaign)" icon-only :label="'Delete '.$campaign->subject" confirm="Delete this campaign?" />
                    @endif
                </td>
            </tr>
        @endforeach
    </x-data-table>
</x-layouts.admin>
