<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', ['settings' => array_merge(Setting::DEFAULTS, Setting::allCached())]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'store_name' => ['required', 'string', 'max:100'],
            'store_email' => ['required', 'email', 'max:255'],
            'currency' => ['required', 'string', 'size:3', 'alpha'],
            'currency_symbol' => ['required', 'string', 'max:5'],
            'shipping_flat_rate' => ['required', 'numeric', 'min:0', 'max:10000'],
            'free_shipping_over' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $data['currency'] = strtolower($data['currency']);
        Setting::putMany($data);

        return back()->with('status', 'Settings saved.');
    }
}
