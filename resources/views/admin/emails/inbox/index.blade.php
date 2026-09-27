<x-layouts.admin title="Inbox">
    <x-data-table :rows="$messages" search-placeholder="Search sender, subject or text…" label="messages" empty="No messages yet." :columns="[
        'from_email' => ['label' => 'From', 'sortable' => true],
        'subject' => ['label' => 'Subject', 'sortable' => true],
        'source' => ['label' => 'Via'],
        'created_at' => ['label' => 'Received', 'sortable' => true],
        'actions' => ['label' => '', 'class' => 'text-end'],
    ]">
        <x-slot:filters>
            <select name="filter" class="form-select form-select-sm w-auto" aria-label="Show" data-auto-submit>
                <option value="">All messages</option>
                <option value="unread" @selected(request('filter') === 'unread')>Unread</option>
                <option value="unanswered" @selected(request('filter') === 'unanswered')>Not replied</option>
            </select>
            <select name="source" class="form-select form-select-sm w-auto" aria-label="Filter by source" data-auto-submit>
                <option value="">Email & contact form</option>
                <option value="email" @selected(request('source') === 'email')>Email</option>
                <option value="contact" @selected(request('source') === 'contact')>Contact form</option>
            </select>
        </x-slot:filters>
        @foreach ($messages as $message)
            <tr class="{{ $message->isUnread() ? 'fw-semibold' : '' }}">
                <td>
                    @if ($message->isUnread())<span class="d-inline-block rounded-circle me-1" style="width:.5rem;height:.5rem;background:var(--ms-primary)" title="Unread"></span><span class="visually-hidden">Unread:</span>@endif
                    {{ $message->from_name ?: $message->from_email }}
                    @if ($message->from_name)<div class="small text-body-secondary fw-normal">{{ $message->from_email }}</div>@endif
                </td>
                <td>
                    <a href="{{ route('admin.emails.inbox.show', $message) }}" class="text-reset">{{ $message->subject ?: '(no subject)' }}</a>
                    <div class="small text-body-secondary fw-normal text-truncate" style="max-width: 28rem">{{ \Illuminate\Support\Str::limit($message->body, 90) }}</div>
                </td>
                <td class="small fw-normal">{{ $message->source === 'contact' ? 'Contact form' : 'Email' }} @if ($message->replies_count)<i class="bi bi-reply-fill text-success" title="Replied"></i>@endif</td>
                <td class="small text-body-secondary fw-normal text-nowrap">{{ $message->created_at->diffForHumans() }}</td>
                <td class="text-end"><a href="{{ route('admin.emails.inbox.show', $message) }}" class="btn btn-sm btn-soft">Open</a></td>
            </tr>
        @endforeach
    </x-data-table>
</x-layouts.admin>
