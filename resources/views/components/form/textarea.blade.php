@props(['name', 'label', 'value' => null, 'rows' => 4, 'help' => null, 'required' => false])
@php($id = 'f-'.$name)
<div {{ $attributes->only('class')->merge(['class' => 'mb-3']) }}>
    <label for="{{ $id }}" class="form-label">{{ $label }}@if ($required) <span class="text-danger" aria-hidden="true">*</span>@endif</label>
    <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" {{ $attributes->except('class')->class(['form-control', 'is-invalid' => $errors->has($name)]) }} @if ($required) required @endif>{{ old($name, $value) }}</textarea>
    @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
