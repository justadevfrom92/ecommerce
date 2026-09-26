@props(['action', 'confirm' => 'Delete this? This cannot be undone.', 'label' => 'Delete', 'size' => 'sm', 'iconOnly' => false])
<form method="POST" action="{{ $action }}" class="d-inline" data-confirm="{{ $confirm }}">
    @csrf @method('DELETE')
    <button type="submit" {{ $attributes->class(["btn btn-$size btn-outline-danger"]) }} @if ($iconOnly) aria-label="{{ $label }}" @endif>
        <i class="bi bi-trash"></i>@unless ($iconOnly) {{ $label }}@endunless
    </button>
</form>
