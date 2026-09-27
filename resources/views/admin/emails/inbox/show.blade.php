<x-layouts.admin :title="$message->subject ?: '(no subject)'">
    <x-slot:actions>
        <form method="POST" action="{{ route('admin.emails.inbox.unread', $message) }}">
            @csrf
            <button class="btn btn-sm btn-soft"><i class="bi bi-envelope me-1"></i>Mark unread</button>
        </form>
        <x-delete-button :action="route('admin.emails.inbox.destroy', $message)" confirm="Delete this message and its replies?" />
    </x-slot:actions>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-body py-3 d-flex flex-wrap justify-content-between gap-2">
                    <div>
                        <span class="fw-semibold">{{ $message->from_name ?: $message->from_email }}</span>
                        <span class="small text-body-secondary">&lt;{{ $message->from_email }}&gt;</span>
                        @if ($message->user && auth()->user()->can('users.manage'))
                            <a href="{{ route('admin.users.edit', $message->user) }}" class="pill pill-neutral ms-1">Customer</a>
                        @endif
                    </div>
                    <span class="small text-body-secondary">{{ $message->created_at->format('M j, Y g:i A') }} · {{ $message->source === 'contact' ? 'Contact form' : 'Email' }}</span>
                </div>
                <div class="card-body" style="white-space: pre-wrap">{{ $message->body }}</div>
            </div>

            @foreach ($message->replies as $reply)
                <div class="card border-0 shadow-sm mb-3 ms-4 border-start border-primary border-3">
                    <div class="card-header bg-body py-2 small d-flex justify-content-between">
                        <span><i class="bi bi-reply me-1"></i>{{ $reply->user?->name ?? 'Staff' }} replied</span>
                        <span class="text-body-secondary">{{ $reply->created_at->format('M j, Y g:i A') }}</span>
                    </div>
                    <div class="card-body small" style="white-space: pre-wrap">{{ $reply->body }}</div>
                </div>
            @endforeach

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h2 class="h6 mb-3">Reply to {{ $message->from_email }}</h2>
                    <form method="POST" action="{{ route('admin.emails.inbox.reply', $message) }}">
                        @csrf
                        <x-form.textarea name="body" label="Message" rows="6" required help="Sent from your store address; their original message is quoted below your reply." />
                        <button class="btn btn-primary"><i class="bi bi-send me-1"></i>Send reply</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <a href="{{ route('admin.emails.inbox.index') }}" class="small"><i class="bi bi-arrow-left me-1"></i>Back to inbox</a>
        </div>
    </div>
</x-layouts.admin>
