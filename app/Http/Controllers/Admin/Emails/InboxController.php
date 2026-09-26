<?php

namespace App\Http\Controllers\Admin\Emails;

use App\Http\Controllers\Controller;
use App\Mail\StoreMail;
use App\Models\InboundMessage;
use App\Services\Emailer;
use App\Support\DataTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InboxController extends Controller
{
    public function index(Request $request): View
    {
        $query = InboundMessage::query()->withCount('replies');

        match ($request->query('filter')) {
            'unread' => $query->whereNull('read_at'),
            'unanswered' => $query->whereNull('replied_at'),
            default => null,
        };

        if (in_array($request->query('source'), ['email', 'contact'], true)) {
            $query->where('source', $request->query('source'));
        }

        $messages = DataTable::paginate($query, $request,
            searchable: ['from_email', 'from_name', 'subject', 'body'],
            sortable: ['from_email', 'subject', 'created_at'],
            defaultSort: 'created_at',
        );

        return view('admin.emails.inbox.index', [
            'messages' => $messages,
            'unreadCount' => InboundMessage::whereNull('read_at')->count(),
        ]);
    }

    public function show(InboundMessage $message): View
    {
        if ($message->isUnread()) {
            $message->update(['read_at' => now()]);
        }

        return view('admin.emails.inbox.show', ['message' => $message->load('replies.user', 'user')]);
    }

    public function reply(Request $request, InboundMessage $message, Emailer $emailer): RedirectResponse
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:20000']]);

        $subject = Str::startsWith(Str::lower((string) $message->subject), 're:') ? $message->subject : 'Re: '.($message->subject ?: 'Your message');
        $quoted = "\n\n— On {$message->created_at->format('M j, Y g:i A')}, {$message->from_email} wrote:\n> ".str_replace("\n", "\n> ", Str::limit($message->body, 3000));

        $ok = $emailer->send($message->from_email, new StoreMail(mailSubject: $subject, body: $data['body'].$quoted, templateKey: 'inbox_reply'));

        if (! $ok) {
            return back()->withInput()->with('error', 'The reply could not be sent. Check the outgoing log and mail settings.');
        }

        $message->replies()->create(['user_id' => $request->user()->id, 'body' => $data['body']]);
        $message->update(['replied_at' => now()]);

        return back()->with('status', "Reply sent to {$message->from_email}.");
    }

    public function markUnread(InboundMessage $message): RedirectResponse
    {
        $message->update(['read_at' => null]);

        return redirect()->route('admin.emails.inbox.index')->with('status', 'Marked as unread.');
    }

    public function destroy(InboundMessage $message): RedirectResponse
    {
        $message->delete();

        return redirect()->route('admin.emails.inbox.index')->with('status', 'Message deleted.');
    }
}
