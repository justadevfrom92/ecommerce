<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Support\DataTable;
use App\Support\Slug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    /** Roles the app relies on; they can be edited but not deleted. */
    private const PROTECTED = ['super-admin', 'customer'];

    public function index(Request $request): View
    {
        $roles = DataTable::paginate(
            Role::query()->withCount(['users', 'permissions']),
            $request,
            searchable: ['name', 'description'],
            sortable: ['name', 'created_at'],
            defaultSort: 'name',
            defaultDirection: 'asc',
        );

        return view('admin.roles.index', ['roles' => $roles, 'protected' => self::PROTECTED]);
    }

    public function create(): View
    {
        return view('admin.roles.form', ['role' => new Role, 'groups' => $this->groups(), 'selected' => []]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $role = Role::create([
            'name' => $data['name'],
            'slug' => Slug::unique(Role::class, $data['name']),
            'description' => $data['description'] ?? null,
            'is_super' => $request->user()->isSuperAdmin() && $request->boolean('is_super'),
        ]);
        $role->permissions()->sync($data['permissions'] ?? []);

        return redirect()->route('admin.roles.index')->with('status', "Role {$role->name} created.");
    }

    public function edit(Role $role): View
    {
        return view('admin.roles.form', [
            'role' => $role,
            'groups' => $this->groups(),
            'selected' => $role->permissions()->pluck('permissions.id')->all(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        abort_if($role->is_super && ! $request->user()->isSuperAdmin(), 403, 'Only a super admin can change a super role.');

        $data = $this->validated($request);
        $update = ['name' => $data['name'], 'description' => $data['description'] ?? null];

        // Only super admins toggle the super flag, and the built-in super role always stays super.
        if ($request->user()->isSuperAdmin() && $role->slug !== 'super-admin') {
            $update['is_super'] = $request->boolean('is_super');
        }

        $role->update($update);
        $role->permissions()->sync($data['permissions'] ?? []);

        return redirect()->route('admin.roles.edit', $role)->with('status', 'Role saved.');
    }

    public function destroy(Request $request, Role $role): RedirectResponse
    {
        if (in_array($role->slug, self::PROTECTED, true)) {
            return back()->with('error', "The {$role->name} role is built in and can't be deleted.");
        }

        abort_if($role->is_super && ! $request->user()->isSuperAdmin(), 403);

        $role->delete();

        return redirect()->route('admin.roles.index')->with('status', "Deleted role {$role->name}.");
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);
    }

    private function groups()
    {
        return Permission::orderBy('id')->get()->groupBy('group');
    }
}
