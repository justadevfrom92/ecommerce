<x-layouts.admin :title="'Template: '.$template->name">
    <x-slot:actions>
        <form method="POST" action="{{ route('admin.emails.templates.test', $template) }}">
            @csrf
            <button class="btn btn-sm btn-soft"><i class="bi bi-send me-1"></i>Send me a test</button>
        </form>
        <form method="POST" action="{{ route('admin.emails.templates.reset', $template) }}" data-confirm="Replace your wording with the default text?">
            @csrf
            <button class="btn btn-sm btn-soft">Reset to default</button>
        </form>
    </x-slot:actions>

    <div class="row g-4">
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <p class="small text-body-secondary">{{ $template->description }}</p>
                    <form method="POST" action="{{ route('admin.emails.templates.update', $template) }}">
                        @csrf @method('PUT')
                        <x-form.input name="subject" label="Subject" :value="$template->subject" required />
                        <x-form.textarea name="body" label="Message" :value="$template->body" rows="12" required help="Plain text. Blank lines start a new paragraph; web addresses become links." />
                        <x-form.input name="button_text" label="Button text" :value="$template->button_text" help="Leave blank for no button. The button links to the right page automatically." />
                        <div class="mb-3">
                            <div class="form-label small">Placeholders you can use</div>
                            @foreach ($template->placeholders() as $placeholder)
                                <code class="me-2">{{ '{'.'{'.$placeholder.'}'.'}' }}</code>
                            @endforeach
                        </div>
                        <button class="btn btn-primary">Save template</button>
                        <a href="{{ route('admin.emails.templates.index') }}" class="btn btn-link">Back</a>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            @include('admin.emails._preview', $preview)
            <p class="small text-body-secondary mt-2">The preview uses sample data and shows your last saved version.</p>
        </div>
    </div>
</x-layouts.admin>
