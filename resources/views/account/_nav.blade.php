<div class="list-group shadow-sm">
    <a href="{{ route('account.edit') }}" class="list-group-item list-group-item-action @if (request()->routeIs('account.edit')) active @endif"><i class="bi bi-person me-2"></i>Profile</a>
    @if (Route::has('account.orders.index'))
        <a href="{{ route('account.orders.index') }}" class="list-group-item list-group-item-action @if (request()->routeIs('account.orders.*')) active @endif"><i class="bi bi-bag me-2"></i>Orders</a>
    @endif
    <a href="{{ route('account.password.edit') }}" class="list-group-item list-group-item-action @if (request()->routeIs('account.password.*')) active @endif"><i class="bi bi-key me-2"></i>Password</a>
</div>
