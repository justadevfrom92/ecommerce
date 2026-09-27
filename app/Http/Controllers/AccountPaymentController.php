<?php

namespace App\Http\Controllers;

use App\Services\Stripe;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/** Saved payment methods. Card details are entered on and stored by Stripe only. */
class AccountPaymentController extends Controller
{
    public function __construct(private Stripe $stripe) {}

    public function index(Request $request): View
    {
        $methods = [];
        $error = null;

        if ($this->stripe->enabled()) {
            try {
                $methods = $this->stripe->paymentMethods($request->user());
            } catch (Throwable $e) {
                Log::warning('Could not load payment methods', ['user' => $request->user()->id, 'error' => $e->getMessage()]);
                $error = 'We couldn\'t load your saved payment methods right now. Please try again shortly.';
            }
        }

        return view('account.payments', [
            'user' => $request->user(),
            'methods' => $methods,
            'enabled' => $this->stripe->enabled(),
            'error' => $error,
        ]);
    }

    public function store(Request $request): Response
    {
        abort_unless($this->stripe->enabled(), 404);

        try {
            $url = $this->stripe->createSetupSession(
                $request->user(),
                route('account.payments.index').'?added=1',
                route('account.payments.index'),
            );
        } catch (Throwable $e) {
            Log::error('Stripe setup session failed', ['user' => $request->user()->id, 'error' => $e->getMessage()]);

            return back()->with('error', 'We couldn\'t reach the payment provider. Please try again shortly.');
        }

        return redirect()->away($url, 303);
    }

    public function makeDefault(Request $request, string $method): RedirectResponse
    {
        abort_unless($this->stripe->enabled(), 404);

        try {
            $this->stripe->setDefaultPaymentMethod($request->user(), $method);
        } catch (Throwable $e) {
            return back()->with('error', 'That payment method couldn\'t be set as default.');
        }

        return back()->with('status', 'Default payment method updated.');
    }

    public function destroy(Request $request, string $method): RedirectResponse
    {
        abort_unless($this->stripe->enabled(), 404);

        try {
            $this->stripe->detachPaymentMethod($request->user(), $method);
        } catch (Throwable $e) {
            return back()->with('error', 'That payment method couldn\'t be removed.');
        }

        return back()->with('status', 'Payment method removed.');
    }
}
