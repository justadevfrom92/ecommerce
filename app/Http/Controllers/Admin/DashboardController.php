<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        // Everyone with admin access lands here; only dashboard.view sees the numbers.
        if ($request->user()->cannot('dashboard.view')) {
            return view('admin.dashboard', ['stats' => null, 'lowStock' => collect()]);
        }

        return view('admin.dashboard', [
            'stats' => [
                ['label' => 'Products', 'value' => Product::count(), 'icon' => 'box-seam', 'color' => 'primary'],
                ['label' => 'Departments', 'value' => Department::count(), 'icon' => 'diagram-3', 'color' => 'info'],
                ['label' => 'Users', 'value' => User::count(), 'icon' => 'people', 'color' => 'success'],
                ['label' => 'Low stock (≤ 5)', 'value' => Product::where('stock', '<=', 5)->count(), 'icon' => 'exclamation-triangle', 'color' => 'warning'],
            ],
            'lowStock' => Product::with('category')->where('stock', '<=', 5)->orderBy('stock')->limit(8)->get(),
        ]);
    }
}
