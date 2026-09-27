<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** /admin has no page of its own: it opens the first section the user's role can use. */
class DashboardController extends Controller
{
    private const SECTIONS = [
        'orders.view' => 'admin.orders.index',
        'products.view' => 'admin.products.index',
        'emails.inbox' => 'admin.emails.inbox.index',
        'users.view' => 'admin.users.index',
        'departments.manage' => 'admin.departments.index',
        'categories.manage' => 'admin.categories.index',
        'roles.manage' => 'admin.roles.index',
        'emails.log' => 'admin.emails.log.index',
        'emails.templates' => 'admin.emails.templates.index',
        'settings.manage' => 'admin.settings.edit',
    ];

    public function __invoke(Request $request): RedirectResponse
    {
        foreach (self::SECTIONS as $permission => $route) {
            if ($request->user()->can($permission)) {
                return redirect()->route($route);
            }
        }

        return redirect()->route('home')->with('error', 'Your role doesn’t include any admin sections yet.');
    }
}
