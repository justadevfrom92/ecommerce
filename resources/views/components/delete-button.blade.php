@props(['action', 'confirm' => 'Delete this? This cannot be undone.', 'label' => 'Delete', 'size' => 'sm', 'iconOnly' => false])
<form method="POST" action="{{ $action }}" class="d-inline" data-confirm="{{ $confirm }}">
    @csrf @method('DELETE')
    <button type="submit" {{ $attributes->class(["btn btn-$size btn-soft-danger", 'btn-icon' => $iconOnly]) }} @if ($iconOnly) aria-label="{{ $label }}" title="{{ $label }}" @endif>
        <i class="bi bi-trash"></i>@unless ($iconOnly)<span class="ms-1">{{ $label }}</span>@endunless
    </button>
</form>
