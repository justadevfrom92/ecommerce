<x-layouts.admin title="Newsletter subscribers">
    <x-slot:actions>
        <a href="{{ route('admin.emails.subscribers.export') }}" class="btn btn-sm btn-soft"><i class="bi bi-download me-1"></i>Export CSV</a>
    </x-slot:actions>

    <div class="row g-4">
        <div class="col-xl-9">
            <x-data-table :rows="$subscribers" search-placeholder="Search email or name…" label="subscribers" :columns="[
                'email' => ['label' => 'Email', 'sortable' => true],
                'name' => ['label' => 'Name', 'sortable' => true],
                'source' => ['label' => 'Source'],
                'status' => ['label' => 'Status', 'sortable' => true],
                'subscribed_at' => ['label' => 'Subscribed', 'sortable' => true],
                'actions' => ['label' => '', 'class' => 'text-end'],
            ]">
                <x-slot:filters>
                    <select name="status" class="form-select form-select-sm w-auto" aria-label="Filter by status" data-auto-submit>
                        <option value="">Any status</option>
                        <option value="subscribed" @selected(request('status') === 'subscribed')>Subscribed</option>
                        <option value="unsubscribed" @selected(request('status') === 'unsubscribed')>Unsubscribed</option>
                    </select>
                </x-slot:filters>
                @foreach ($subscribers as $subscriber)
                    <tr>
                        <td class="small">{{ $subscriber->email }}</td>
                        <td class="small">{{ $subscriber->name ?? '—' }}</td>
                        <td class="small text-body-secondary">{{ ucfirst($subscriber->source ?? '—') }}</td>
                        <td><span class="{{ $subscriber->status === 'subscribed' ? 'pill pill-success' : 'pill pill-neutral' }}">{{ $subscriber->status }}</span></td>
                        <td class="small text-body-secondary">{{ $subscriber->subscribed_at?->format('M j, Y') ?? '—' }}</td>
                        <td class="text-end text-nowrap">
                            <form method="POST" action="{{ route('admin.emails.subscribers.update', $subscriber) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm btn-soft">{{ $subscriber->status === 'subscribed' ? 'Unsubscribe' : 'Resubscribe' }}</button>
                            </form>
                            <x-delete-button :action="route('admin.emails.subscribers.destroy', $subscriber)" icon-only :label="'Remove '.$subscriber->email" :confirm="'Remove '.$subscriber->email.' completely?'" />
                        </td>
                    </tr>
                @endforeach
            </x-data-table>
        </div>
        <div class="col-xl-3">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="small text-body-secondary">Active subscribers</div>
                    <div class="fs-3 fw-bold">{{ number_format($activeCount) }}</div>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h6 mb-3">Add subscriber</h2>
                    <form method="POST" action="{{ route('admin.emails.subscribers.store') }}">
                        @csrf
                        <x-form.input name="email" label="Email" type="email" required />
                        <x-form.input name="name" label="Name" />
                        <p class="small text-body-secondary">Only add people who agreed to receive your emails.</p>
                        <button class="btn btn-primary btn-sm w-100">Add</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
