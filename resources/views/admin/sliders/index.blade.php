<x-layouts.admin title="Homepage sliders">
    <p class="text-body-secondary">These product sliders appear on the homepage from top to bottom, in sort order.</p>
    <x-data-table :rows="$sliders" search-placeholder="Search sliders…" label="sliders" :columns="[
        'sort_order' => ['label' => 'Order', 'sortable' => true],
        'title' => ['label' => 'Title', 'sortable' => true],
        'source' => ['label' => 'Shows'],
        'max_items' => ['label' => 'Max items', 'class' => 'text-end'],
        'status' => ['label' => 'Status'],
        'actions' => ['label' => '', 'class' => 'text-end'],
    ]">
        <x-slot:toolbar>
            <a href="{{ route('admin.sliders.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add slider</a>
        </x-slot:toolbar>
        @foreach ($sliders as $slider)
            <tr>
                <td>{{ $slider->sort_order }}</td>
                <td class="fw-semibold">{{ $slider->title }}</td>
                <td class="small">
                    {{ \App\Models\HomeSlider::SOURCES[$slider->source] ?? $slider->source }}
                    @if ($slider->source === 'department')<span class="text-body-secondary">: {{ $departments[$slider->source_id] ?? 'deleted' }}</span>@endif
                    @if ($slider->source === 'category')<span class="text-body-secondary">: {{ $categories[$slider->source_id] ?? 'deleted' }}</span>@endif
                </td>
                <td class="text-end">{{ $slider->max_items }}</td>
                <td><span class="badge {{ $slider->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $slider->is_active ? 'Showing' : 'Hidden' }}</span></td>
                <td class="text-end text-nowrap">
                    <a href="{{ route('admin.sliders.edit', $slider) }}" class="btn btn-sm btn-outline-secondary" aria-label="Edit {{ $slider->title }}"><i class="bi bi-pencil"></i></a>
                    <x-delete-button :action="route('admin.sliders.destroy', $slider)" icon-only :label="'Delete '.$slider->title" :confirm="'Remove the '.$slider->title.' slider from the homepage?'" />
                </td>
            </tr>
        @endforeach
    </x-data-table>
</x-layouts.admin>
