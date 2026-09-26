<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Support\DataTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->with('roles')->withCount('orders');

        if ($role = $request->query('role')) {
            $query->whereHas('roles', fn ($q) => $q->where('slug', $role));
        }

        if (in_array($request->query('status'), ['active', 'inactive'], true)) {
            $query->where('is_active', $request->query('status') === 'active');
        }

        $users = DataTable::paginate($query, $request,
            searchable: ['name', 'email'],
            sortable: ['name', 'email', 'created_at', 'last_login_at'],
            defaultSort: 'created_at',
        );

        return view('admin.users.index', [
            'users' => $users,
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.users.form', [
            'user' => new User(['is_active' => true]),
            'roles' => $this->assignableRoles($request->user()),
            'userRoleIds' => Role::where('slug', 'customer')->pluck('id')->all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'is_active' => $request->boolean('is_active'),
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();
        $user->roles()->sync($data['roles'] ?? []);

        return redirect()->route('admin.users.index')->with('status', "Created {$user->name}.");
    }

    public function edit(Request $request, User $user): View
    {
        $this->guardSuper($request->user(), $user);

        return view('admin.users.form', [
            'user' => $user,
            'roles' => $this->assignableRoles($request->user()),
            'userRoleIds' => $user->roles->pluck('id')->all(),
            'orders' => $user->orders()->latest()->limit(5)->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->guardSuper($request->user(), $user);
        $data = $this->validated($request, $user);
        $isSelf = $request->user()->is($user);

        $user->fill(['name' => $data['name'], 'email' => $data['email']]);
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }
        // You can't lock yourself out.
        if (! $isSelf) {
            $user->is_active = $request->boolean('is_active');
        }
        $user->save();

        $roleIds = collect($data['roles'] ?? [])->map(fn ($id) => (int) $id);

        // Keep roles the editor isn't allowed to assign (e.g. a manager editing a super admin's other roles).
        $locked = $user->roles->pluck('id')->diff($this->assignableRoles($request->user())->pluck('id'));
        $roleIds = $roleIds->merge($locked);

        // Never remove the last super admin, and never strip your own super role.
        $superIds = Role::where('is_super', true)->pluck('id');
        $losingSuper = $user->isSuperAdmin() && $roleIds->intersect($superIds)->isEmpty();
        if ($losingSuper && ($isSelf || $this->superAdminCount() <= 1)) {
            $roleIds = $roleIds->merge($user->roles->whereIn('id', $superIds)->pluck('id'));
            session()->flash('error', $isSelf ? "You can't remove your own super admin role." : "You can't remove the last super admin.");
        }

        $user->roles()->sync($roleIds->unique()->values()->all());

        return redirect()->route('admin.users.edit', $user)->with('status', 'User saved.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->guardSuper($request->user(), $user);

        if ($request->user()->is($user)) {
            return back()->with('error', "You can't delete your own account here.");
        }

        if ($user->isSuperAdmin() && $this->superAdminCount() <= 1) {
            return back()->with('error', "You can't delete the last super admin.");
        }

        // Orders keep their snapshot (user_id is set to null).
        $user->delete();

        return redirect()->route('admin.users.index')->with('status', "Deleted {$user->name}.");
    }

    private function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user?->id)],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::defaults()],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', Rule::in($this->assignableRoles($request->user())->pluck('id'))],
        ]);
    }

    /** Only a super admin may hand out super roles. */
    private function assignableRoles(User $editor)
    {
        return Role::query()
            ->when(! $editor->isSuperAdmin(), fn ($q) => $q->where('is_super', false))
            ->orderByDesc('is_super')->orderBy('name')->get();
    }

    /** Non-super admins can't edit super admins. */
    private function guardSuper(User $editor, User $target): void
    {
        abort_if($target->isSuperAdmin() && ! $editor->isSuperAdmin(), 403, 'Only a super admin can change a super admin.');
    }

    private function superAdminCount(): int
    {
        return User::where('is_active', true)->whereHas('roles', fn ($q) => $q->where('is_super', true))->count();
    }
}
