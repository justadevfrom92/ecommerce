<?php

namespace App\Http\Controllers;

use App\Models\InboundMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function create(Request $request): View
    {
        return view('store.contact', ['user' => $request->user()]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Hidden "website" field: real people leave it empty, bots fill it in.
        if (filled($request->input('website'))) {
            return back()->with('status', 'Thanks! We\'ll get back to you soon.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        InboundMessage::create([
            'source' => 'contact',
            'from_name' => $data['name'],
            'from_email' => strtolower($data['email']),
            'subject' => $data['subject'],
            'body' => $data['message'],
            'user_id' => $request->user()?->id,
        ]);

        return back()->with('status', 'Thanks! We\'ll get back to you soon.');
    }
}
