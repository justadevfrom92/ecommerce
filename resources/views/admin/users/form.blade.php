@php($editing = $user->exists)
@php($isSelf = $editing && auth()->user()->is($user))
<x-layouts.admin :title="$editing ? 'Edit user' : 'Add user'">
    <x-slot:actions>
        @if ($editing && ! $isSelf)
            <x-delete-button :action="route('admin.users.destroy', $user)" :confirm="'Delete '.$user->name.'? Their orders are kept.'" />
        @endif
    </x-slot:actions>

    <form method="POST" action="{{ $editing ? route('admin.users.update', $user) : route('admin.users.store') }}" class="row g-4">
        @csrf
        @if ($editing) @method('PUT') @endif
        <div class="col-xl-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h6 mb-3">Details</h2>
                    <div class="row">
                        <x-form.input class="col-md-6" name="name" label="Name" :value="$user->name" required />
                        <x-form.input class="col-md-6" name="email" label="Email" type="email" :value="$user->email" required />
                        <x-form.input class="col-md-6" name="password" label="{{ $editing ? 'New password' : 'Password' }}" type="password" autocomplete="new-password" :required="! $editing" :help="$editing ? 'Leave blank to keep the current password.' : null" />
                        <x-form.input class="col-md-6" name="password_confirmation" label="Confirm password" type="password" autocomplete="new-password" />
                    </div>
                    @if ($isSelf)
                        <p class="small text-body-secondary mb-0"><i class="bi bi-info-circle me-1"></i>You can't deactivate your own account.</p>
                    @else
                        <x-form.check name="is_active" label="Active (can log in)" :checked="$user->is_active" />
                    @endif
                </div>
            </div>
            @if ($editing && isset($orders) && $orders->isNotEmpty())
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-body py-3"><h2 class="h6 mb-0">Recent orders</h2></div>
                    <ul class="list-group list-group-flush small">
                        @foreach ($orders as $order)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                @can('orders.view')<a href="{{ route('admin.orders.show', $order) }}">{{ $order->number }}</a>@else {{ $order->number }} @endcan
                                {{ $order->statusPill() }}
                                <span>{{ money($order->total) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
        <div class="col-xl-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h6 mb-1">Roles</h2>
                    <p class="small text-body-secondary">A user gets every permission from all of their roles.</p>
                    @error('roles.*')<div class="alert alert-danger py-2 small">{{ $message }}</div>@enderror
                    @php($checkedRoles = collect(old('roles', $userRoleIds))->map(fn ($id) => (int) $id)->all())
                    @foreach ($roles as $role)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}" id="role-{{ $role->id }}" @checked(in_array($role->id, $checkedRoles, true))>
                            <label class="form-check-label" for="role-{{ $role->id }}">
                                {{ $role->name }} @if ($role->is_super)<span class="pill pill-dark">Super</span>@endif
                                @if ($role->description)<span class="d-block small text-body-secondary">{{ $role->description }}</span>@endif
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-primary">{{ $editing ? 'Save user' : 'Create user' }}</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-link">Back to users</a>
            </div>
        </div>
    </form>
</x-layouts.admin>
