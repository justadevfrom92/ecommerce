<x-layouts.admin title="Users">
    <x-data-table :rows="$users" search-placeholder="Search name or email…" label="users" :columns="[
        'name' => ['label' => 'Name', 'sortable' => true],
        'email' => ['label' => 'Email', 'sortable' => true],
        'roles' => ['label' => 'Roles'],
        'status' => ['label' => 'Status'],
        'last_login_at' => ['label' => 'Last login', 'sortable' => true],
        'created_at' => ['label' => 'Joined', 'sortable' => true],
        'actions' => ['label' => '', 'class' => 'text-end'],
    ]">
        <x-slot:filters>
            <select name="role" class="form-select form-select-sm w-auto" aria-label="Filter by role" data-auto-submit>
                <option value="">All roles</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->slug }}" @selected(request('role') === $role->slug)>{{ $role->name }}</option>
                @endforeach
            </select>
            <select name="status" class="form-select form-select-sm w-auto" aria-label="Filter by status" data-auto-submit>
                <option value="">Any status</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
        </x-slot:filters>
        @if (Route::has('admin.users.create'))
            <x-slot:toolbar>
                @can('users.manage')
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add user</a>
                @endcan
            </x-slot:toolbar>
        @endif

        @foreach ($users as $user)
            <tr>
                <td class="fw-semibold">{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    @forelse ($user->roles as $role)
                        <span class="badge {{ $role->is_super ? 'text-bg-dark' : 'text-bg-light border' }}">{{ $role->name }}</span>
                    @empty
                        <span class="text-body-secondary small">—</span>
                    @endforelse
                </td>
                <td>
                    <span class="badge {{ $user->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
                </td>
                <td class="text-body-secondary small">{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</td>
                <td class="text-body-secondary small">{{ $user->created_at->format('M j, Y') }}</td>
                <td class="text-end text-nowrap">
                    @if (Route::has('admin.users.edit'))
                        @can('users.manage')
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-secondary" aria-label="Edit {{ $user->name }}"><i class="bi bi-pencil"></i></a>
                        @endcan
                    @endif
                </td>
            </tr>
        @endforeach
    </x-data-table>
</x-layouts.admin>
