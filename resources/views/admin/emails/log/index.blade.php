<x-layouts.admin title="Outgoing email log">
    <x-data-table :rows="$logs" search-placeholder="Search recipient or subject…" label="emails" :columns="[
        'created_at' => ['label' => 'Sent', 'sortable' => true],
        'to' => ['label' => 'To', 'sortable' => true],
        'subject' => ['label' => 'Subject', 'sortable' => true],
        'type' => ['label' => 'Type'],
        'status' => ['label' => 'Status', 'sortable' => true],
    ]">
        <x-slot:filters>
            <select name="status" class="form-select form-select-sm w-auto" aria-label="Filter by status" data-auto-submit>
                <option value="">Any status</option>
                @foreach (array_keys(\App\Models\EmailLog::STATUSES) as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <select name="type" class="form-select form-select-sm w-auto" aria-label="Filter by type" data-auto-submit>
                <option value="">Any type</option>
                @foreach (\App\Models\EmailTemplate::DEFAULTS as $key => $t)
                    <option value="{{ $key }}" @selected(request('type') === $key)>{{ $t['name'] }}</option>
                @endforeach
                <option value="inbox_reply" @selected(request('type') === 'inbox_reply')>Inbox replies</option>
            </select>
        </x-slot:filters>
        @foreach ($logs as $log)
            <tr>
                <td class="small text-body-secondary text-nowrap">{{ $log->created_at->format('M j, Y g:i A') }}</td>
                <td class="small">{{ $log->to }}</td>
                <td class="small">{{ \Illuminate\Support\Str::limit($log->subject, 70) }}</td>
                <td class="small text-body-secondary">
                    @if ($log->template_key)
                        {{ \App\Models\EmailTemplate::DEFAULTS[$log->template_key]['name'] ?? \Illuminate\Support\Str::headline($log->template_key) }}
                    @else
                        —
                    @endif
                </td>
                <td>
                    <span class="{{ $log->badge() }}">{{ $log->status }}</span>
                    @if ($log->error)<div class="small text-danger text-truncate" style="max-width: 18rem" title="{{ $log->error }}">{{ $log->error }}</div>@endif
                </td>
            </tr>
        @endforeach
    </x-data-table>
</x-layouts.admin>
