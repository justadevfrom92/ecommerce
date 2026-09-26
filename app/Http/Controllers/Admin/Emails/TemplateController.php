<?php

namespace App\Http\Controllers\Admin\Emails;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Services\Emailer;
use App\Support\DataTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TemplateController extends Controller
{
    public function index(Request $request): View
    {
        $templates = DataTable::paginate(EmailTemplate::query(), $request,
            searchable: ['name', 'subject', 'key'],
            sortable: ['name', 'updated_at'],
            defaultSort: 'name',
            defaultDirection: 'asc',
        );

        return view('admin.emails.templates.index', compact('templates'));
    }

    public function edit(EmailTemplate $template, Emailer $emailer): View
    {
        $preview = $emailer->buildTemplate($template->key, $this->sampleVars($template), route('home'));

        // Views render Renderable values to strings, so hand over plain values.
        return view('admin.emails.templates.edit', [
            'template' => $template,
            'preview' => ['subject' => $preview->mailSubject, 'html' => $preview->render()],
        ]);
    }

    public function update(Request $request, EmailTemplate $template): RedirectResponse
    {
        $template->update($request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:20000'],
            'button_text' => ['nullable', 'string', 'max:60'],
        ]));

        return back()->with('status', 'Template saved.');
    }

    public function test(Request $request, EmailTemplate $template, Emailer $emailer): RedirectResponse
    {
        $ok = $emailer->sendTemplate($request->user()->email, $template->key, $this->sampleVars($template), route('home'));

        return back()->with($ok ? 'status' : 'error', $ok
            ? "Test email sent to {$request->user()->email}."
            : 'The test email could not be sent. Check the outgoing log and your mail settings.');
    }

    public function reset(EmailTemplate $template): RedirectResponse
    {
        $defaults = EmailTemplate::DEFAULTS[$template->key] ?? null;
        abort_unless($defaults, 404);

        $template->update(['subject' => $defaults['subject'], 'body' => $defaults['body'], 'button_text' => $defaults['button_text']]);

        return back()->with('status', 'Template reset to the default wording.');
    }

    private function sampleVars(EmailTemplate $template): array
    {
        $samples = [
            'name' => 'Alex Sample',
            'shop_url' => route('home'),
            'order_number' => 'MS-260101-ABCDE',
            'order_total' => money(123.45),
            'order_items' => '2 × Sample Tee — '.money(40)."\n1 × Sample Mug — ".money(12),
            'order_status' => 'Shipped',
            'order_url' => route('home'),
            'reset_url' => route('home'),
            'expires_minutes' => 60,
            'unsubscribe_url' => route('home'),
        ];

        return array_intersect_key($samples, array_flip($template->placeholders()));
    }
}
