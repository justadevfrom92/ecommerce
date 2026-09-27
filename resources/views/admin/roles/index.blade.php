<x-layouts.admin title="Roles & permissions">
    <x-data-table :rows="$roles" search-placeholder="Search roles…" label="roles" :columns="[
        'name' => ['label' => 'Role', 'sortable' => true],
        'permissions' => ['label' => 'Permissions', 'class' => 'text-end'],
        'users' => ['label' => 'Users', 'class' => 'text-end'],
        'actions' => ['label' => '', 'class' => 'text-end'],
    ]">
        <x-slot:toolbar>
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add role</a>
        </x-slot:toolbar>
        @foreach ($roles as $role)
            <tr>
                <td>
                    <div class="fw-semibold">{{ $role->name }} @if ($role->is_super)<span class="pill pill-dark">Super</span>@endif @if (in_array($role->slug, $protected, true))<span class="pill pill-neutral">Built in</span>@endif</div>
                    <div class="small text-body-secondary">{{ $role->description }}</div>
                </td>
                <td class="text-end">{{ $role->is_super ? 'All' : $role->permissions_count }}</td>
                <td class="text-end">
                    @can('users.view')
                        <a href="{{ route('admin.users.index', ['role' => $role->slug]) }}">{{ $role->users_count }}</a>
                    @else
                        {{ $role->users_count }}
                    @endcan
                </td>
                <td class="text-end text-nowrap">
                    @if (! $role->is_super || auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-soft" aria-label="Edit {{ $role->name }}"><i class="bi bi-pencil"></i></a>
                        @unless (in_array($role->slug, $protected, true))
                            <x-delete-button :action="route('admin.roles.destroy', $role)" icon-only :label="'Delete '.$role->name" :confirm="'Delete the '.$role->name.' role? Users keep their other roles.'" />
                        @endunless
                    @endif
                </td>
            </tr>
        @endforeach
    </x-data-table>
</x-layouts.admin>
