<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Support\DataTable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->with('roles');

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
}
