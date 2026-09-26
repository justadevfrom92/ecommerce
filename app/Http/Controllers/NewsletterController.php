<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use App\Services\Emailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsletterController extends Controller
{
    public function store(Request $request, Emailer $emailer): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:255']]);

        if (Subscriber::subscribe($data['email'], $request->user()?->name, 'homepage')) {
            $subscriber = Subscriber::where('email', strtolower(trim($data['email'])))->first();
            $emailer->sendTemplate($subscriber->email, 'newsletter_welcome', ['unsubscribe_url' => $subscriber->unsubscribeUrl()]);
        }

        // Same answer either way so the form can't be used to check who's subscribed.
        return back()->with('status', "Thanks! You're on the list.");
    }

    /** Signed link from emails; POST supports one-click List-Unsubscribe. */
    public function unsubscribe(Request $request, Subscriber $subscriber): View
    {
        $subscriber->unsubscribe();

        return view('store.unsubscribed', ['subscriber' => $subscriber]);
    }
}
