<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function edit(Request $request): View
    {
        return view('account.edit', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:40'],
            'address_line1' => ['nullable', 'required_with:city,postal_code', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'required_with:address_line1', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['nullable', 'required_with:address_line1', 'string', 'max:20'],
            'country' => ['nullable', 'required_with:address_line1', 'string', 'size:2'],
        ]);

        $data['country'] = isset($data['country']) ? strtoupper($data['country']) : null;
        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return back()->with('status', 'Profile updated.');
    }

    /**
     * Password changes happen only through an emailed, single-use reset link
     * (the same secure flow as "Forgot password?"), never on the account page.
     */
    public function sendPasswordReset(Request $request): RedirectResponse
    {
        Password::sendResetLink(['email' => $request->user()->email]);

        return back()->with('status', "We've emailed a password reset link to {$request->user()->email}. It expires in ".config('auth.passwords.users.expire', 60).' minutes.');
    }
}
