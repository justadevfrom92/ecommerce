@props(['name', 'label', 'checked' => false, 'help' => null, 'switch' => true])
@php($id = 'f-'.$name)
<div {{ $attributes->only('class')->merge(['class' => 'mb-3']) }}>
    <div class="form-check {{ $switch ? 'form-switch' : '' }}">
        <input type="hidden" name="{{ $name }}" value="0">
        <input class="form-check-input" type="checkbox" role="{{ $switch ? 'switch' : 'checkbox' }}" id="{{ $id }}" name="{{ $name }}" value="1" @checked(old($name, $checked)) {{ $attributes->except('class') }}>
        <label class="form-check-label" for="{{ $id }}">{{ $label }}</label>
    </div>
</div>
