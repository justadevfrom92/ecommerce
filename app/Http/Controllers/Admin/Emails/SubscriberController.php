<?php

namespace App\Http\Controllers\Admin\Emails;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use App\Support\DataTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubscriberController extends Controller
{
    public function index(Request $request): View
    {
        $query = Subscriber::query();

        if (in_array($request->query('status'), ['subscribed', 'unsubscribed'], true)) {
            $query->where('status', $request->query('status'));
        }

        $subscribers = DataTable::paginate($query, $request,
            searchable: ['email', 'name'],
            sortable: ['email', 'name', 'status', 'subscribed_at', 'created_at'],
            defaultSort: 'created_at',
        );

        return view('admin.emails.subscribers.index', [
            'subscribers' => $subscribers,
            'activeCount' => Subscriber::subscribed()->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        Subscriber::subscribe($data['email'], $data['name'] ?? null, 'admin');

        return back()->with('status', "{$data['email']} is subscribed.");
    }

    public function update(Subscriber $subscriber): RedirectResponse
    {
        $subscriber->status === 'subscribed'
            ? $subscriber->unsubscribe()
            : Subscriber::subscribe($subscriber->email, $subscriber->name, $subscriber->source ?? 'admin');

        return back()->with('status', "{$subscriber->email} updated.");
    }

    public function destroy(Subscriber $subscriber): RedirectResponse
    {
        $subscriber->delete();

        return back()->with('status', "Removed {$subscriber->email}.");
    }

    public function export(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['email', 'name', 'status', 'source', 'subscribed_at', 'unsubscribed_at']);
            Subscriber::orderBy('id')->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $s) {
                    // Prefix values that spreadsheets would run as formulas.
                    $row = array_map(fn ($v) => is_string($v) && preg_match('/^[=+\-@]/', $v) ? "'".$v : $v,
                        [$s->email, $s->name, $s->status, $s->source, $s->subscribed_at?->toDateTimeString(), $s->unsubscribed_at?->toDateTimeString()]);
                    fputcsv($out, $row);
                }
            });
            fclose($out);
        }, 'subscribers-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }
}
