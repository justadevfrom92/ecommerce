<x-layouts.store title="Payments">
    <div class="container-xxl py-4">
        @include('account._header')
        <div style="max-width: 48rem">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h2 class="h5 fw-extrabold mb-0">Saved payment methods</h2>
                @if ($enabled)
                    <form method="POST" action="{{ route('account.payments.store') }}">
                        @csrf
                        <button class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add payment method</button>
                    </form>
                @endif
            </div>

            @if (request('added'))
                <div class="alert alert-success">Payment method added.</div>
            @endif
            @if ($error)
                <div class="alert alert-warning">{{ $error }}</div>
            @endif

            @if (! $enabled)
                <div class="panel panel-body text-center py-5">
                    <i class="bi bi-credit-card fs-2 text-muted-2 d-block mb-2"></i>
                    <p class="fw-bold mb-0" style="color: var(--ms-heading)">Online payments aren't switched on for this store yet.</p>
                </div>
            @elseif ($methods === [])
                <div class="panel panel-body text-center py-5">
                    <i class="bi bi-credit-card fs-2 text-muted-2 d-block mb-2"></i>
                    <p class="fw-bold mb-3" style="color: var(--ms-heading)">You have no saved payment methods.</p>
                    <form method="POST" action="{{ route('account.payments.store') }}">
                        @csrf
                        <button class="btn btn-soft btn-sm">Add payment method</button>
                    </form>
                </div>
            @else
                <div class="panel">
                    @foreach ($methods as $pm)
                        <div class="d-flex flex-wrap align-items-center gap-3 px-4 py-3 @unless ($loop->last) border-bottom @endunless">
                            <span class="avatar-sm" style="width: 2.75rem; height: 2rem; border-radius: .35rem">
                                <i class="bi bi-{{ $pm['type'] === 'card' ? 'credit-card-2-front' : ($pm['type'] === 'paypal' ? 'paypal' : 'bank') }}"></i>
                            </span>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-bold" style="color: var(--ms-heading)">{{ $pm['label'] }}
                                    @if ($pm['default'])<span class="pill pill-primary ms-1">Default</span>@endif
                                </div>
                                <div class="small text-muted-2">{{ $pm['detail'] }}</div>
                            </div>
                            <form method="POST" action="{{ route('account.payments.default', $pm['id']) }}" class="form-check mb-0">
                                @csrf
                                <input class="form-check-input" type="checkbox" id="default-{{ $pm['id'] }}" @checked($pm['default']) @disabled($pm['default']) data-auto-submit>
                                <label class="form-check-label small fw-semibold" for="default-{{ $pm['id'] }}">Default payment</label>
                            </form>
                            <x-delete-button :action="route('account.payments.destroy', $pm['id'])" icon-only :label="'Remove '.$pm['label']" :confirm="'Remove '.$pm['label'].'?'" />
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layouts.store>
