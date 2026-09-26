<x-layouts.admin title="Email templates">
    <p class="text-body-secondary">The wording of the automatic emails the store sends. Edit the text; the store fills in the <code>@{{placeholders}}</code>.</p>
    <x-data-table :rows="$templates" search-placeholder="Search templates…" label="templates" :columns="[
        'name' => ['label' => 'Template', 'sortable' => true],
        'subject' => ['label' => 'Subject'],
        'updated_at' => ['label' => 'Last edited', 'sortable' => true],
        'actions' => ['label' => '', 'class' => 'text-end'],
    ]">
        @foreach ($templates as $template)
            <tr>
                <td><div class="fw-semibold">{{ $template->name }}</div><div class="small text-body-secondary">{{ $template->description }}</div></td>
                <td class="small">{{ $template->subject }}</td>
                <td class="small text-body-secondary">{{ $template->updated_at->diffForHumans() }}</td>
                <td class="text-end"><a href="{{ route('admin.emails.templates.edit', $template) }}" class="btn btn-sm btn-outline-secondary">Edit</a></td>
            </tr>
        @endforeach
    </x-data-table>
</x-layouts.admin>
