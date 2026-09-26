@php($editing = $role->exists)
<x-layouts.admin :title="$editing ? 'Edit role' : 'Add role'">
    <form method="POST" action="{{ $editing ? route('admin.roles.update', $role) : route('admin.roles.store') }}" class="row g-4">
        @csrf
        @if ($editing) @method('PUT') @endif
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <x-form.input name="name" label="Role name" :value="$role->name" required maxlength="100" />
                    <x-form.input name="description" label="Description" :value="$role->description" maxlength="255" />
                    @if (auth()->user()->isSuperAdmin() && $role->slug !== 'super-admin')
                        <x-form.check name="is_super" label="Super role (every permission, including future ones)" :checked="$role->is_super" />
                    @endif
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">{{ $editing ? 'Save role' : 'Create role' }}</button>
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-link">Back to roles</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h6 mb-1">Permissions</h2>
                    <p class="small text-body-secondary">Tick what this role can do. <strong>Access the admin area</strong> is needed for any admin section.</p>
                    @if ($role->is_super)
                        <div class="alert alert-info small py-2">Super roles already have every permission, so these boxes don't restrict them.</div>
                    @endif
                    @php($checked = collect(old('permissions', $selected))->map(fn ($id) => (int) $id)->all())
                    <div class="row g-4">
                        @foreach ($groups as $group => $permissions)
                            @php($key = \Illuminate\Support\Str::slug($group))
                            <div class="col-md-6">
                                <fieldset>
                                    <legend class="fs-6 fw-semibold border-bottom pb-1 mb-2 d-flex justify-content-between align-items-center">
                                        {{ $group }}
                                        <span class="form-check small fw-normal mb-0">
                                            <input class="form-check-input" type="checkbox" id="all-{{ $key }}" data-check-group="{{ $key }}">
                                            <label class="form-check-label" for="all-{{ $key }}">All</label>
                                        </span>
                                    </legend>
                                    @foreach ($permissions as $permission)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="perm-{{ $permission->id }}" data-check-member="{{ $key }}" @checked(in_array($permission->id, $checked, true))>
                                            <label class="form-check-label" for="perm-{{ $permission->id }}">
                                                {{ $permission->name }} <code class="small text-body-secondary">{{ $permission->slug }}</code>
                                            </label>
                                        </div>
                                    @endforeach
                                </fieldset>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </form>
</x-layouts.admin>
