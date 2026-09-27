{{-- Profile header shared by the account pages: summary card, address card and tabs. --}}
@php
    $user = auth()->user();
    $initials = \Illuminate\Support\Str::of($user->name)->explode(' ')->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->join('');
    $sold = ['paid', 'processing', 'shipped', 'delivered'];
    $spent = (float) $user->orders()->whereIn('status', $sold)->sum('total');
    $orderCount = $user->orders()->count();
    $lastOrder = $user->orders()->latest()->first();
@endphp
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">My account</li>
    </ol>
</nav>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <h1 class="page-title">Profile</h1>
    <div class="d-flex gap-2">
        <form method="POST" action="{{ route('account.password.reset-link') }}" data-confirm="Email a password reset link to {{ $user->email }}?">
            @csrf
            <button class="btn btn-soft btn-sm"><i class="bi bi-key me-1"></i>Reset password</button>
        </form>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-soft btn-sm"><i class="bi bi-box-arrow-right me-1"></i>Log out</button>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-8">
        <div class="panel panel-body h-100">
            <div class="d-flex flex-wrap align-items-center gap-4 pb-4 mb-3 dashed-bottom">
                <span class="avatar-xl">{{ $initials }}</span>
                <div>
                    <h2 class="h3 fw-extrabold mb-1">{{ $user->name }}</h2>
                    <p class="text-muted-2 mb-0">Joined {{ $user->created_at->diffForHumans() }}</p>
                </div>
            </div>
            <div class="d-flex flex-wrap justify-content-between gap-3">
                <div><div class="stat-label">Total spent</div><div class="stat-value tabular">{{ money($spent) }}</div></div>
                <div class="text-sm-center"><div class="stat-label">Last order</div><div class="stat-value">{{ $lastOrder?->created_at->diffForHumans() ?? 'None yet' }}</div></div>
                <div class="text-sm-end"><div class="stat-label">Total orders</div><div class="stat-value tabular">{{ $orderCount }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="panel panel-body h-100">
            <h2 class="h5 fw-extrabold pb-3 mb-3 dashed-bottom">Default address</h2>
            @php
                $addr = $user->hasAddress()
                    ? [$user->address_line1, $user->address_line2, trim($user->city.', '.$user->state, ', ').' '.$user->postal_code, $user->country]
                    : ($lastOrder ? [$lastOrder->shipping_line1, $lastOrder->shipping_line2, trim($lastOrder->shipping_city.', '.$lastOrder->shipping_state, ', ').' '.$lastOrder->shipping_postal_code, $lastOrder->shipping_country] : []);
                $phone = $user->phone ?: $lastOrder?->shipping_phone;
            @endphp
            <div class="kv mb-3 pb-3 dashed-bottom" style="grid-template-columns: 5rem 1fr">
                <span class="k">Address</span>
                <span class="text-end" style="color: var(--ms-heading)">
                    @forelse (array_filter($addr) as $line)
                        {{ $line }}@unless ($loop->last)<br>@endunless
                    @empty
                        <a href="{{ route('account.edit') }}#address">Add your address</a>
                    @endforelse
                </span>
            </div>
            <div class="kv" style="grid-template-columns: 5rem 1fr">
                <span class="k">Email</span><span class="text-end text-truncate"><span style="color: var(--ms-primary)">{{ $user->email }}</span></span>
                <span class="k">Phone</span><span class="text-end">@if ($phone)<span style="color: var(--ms-primary)">{{ $phone }}</span>@else<a href="{{ route('account.edit') }}#phone">Add your phone</a>@endif</span>
            </div>
        </div>
    </div>
</div>

<ul class="nav icon-tabs mb-4">
    <li class="nav-item"><a href="{{ route('account.orders.index') }}" class="nav-link @if (request()->routeIs('account.orders.*')) active @endif"><i class="bi bi-cart-fill me-1"></i>Orders <span class="count">({{ $orderCount }})</span></a></li>
    <li class="nav-item"><a href="{{ route('account.edit') }}" class="nav-link @if (request()->routeIs('account.edit')) active @endif"><i class="bi bi-person-fill me-1"></i>Personal info</a></li>
    <li class="nav-item"><a href="{{ route('account.payments.index') }}" class="nav-link @if (request()->routeIs('account.payments.*')) active @endif"><i class="bi bi-credit-card-fill me-1"></i>Payments</a></li>
</ul>
