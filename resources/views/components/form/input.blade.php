@props(['name', 'label', 'type' => 'text', 'value' => null, 'help' => null, 'required' => false, 'prefix' => null])
@php($id = $attributes->get('id', 'f-'.str_replace(['[', ']', '.'], '-', $name)))
<div {{ $attributes->only('class')->merge(['class' => 'mb-3']) }}>
    <label for="{{ $id }}" class="form-label">{{ $label }}@if ($required) <span class="text-danger" aria-hidden="true">*</span>@endif</label>
    @if ($prefix)<div class="input-group has-validation"><span class="input-group-text">{{ $prefix }}</span>@endif
    <input type="{{ $type }}" id="{{ $id }}" name="{{ $name }}"
           @if ($type !== 'password' && $type !== 'file') value="{{ old($name, $value) }}" @endif
           {{ $attributes->except(['class', 'id'])->class(['form-control', 'is-invalid' => $errors->has($name)]) }}
           @if ($required) required @endif
           @if ($help) aria-describedby="{{ $id }}-help" @endif>
    @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
    @if ($prefix)</div>@endif
    @if ($help)<div id="{{ $id }}-help" class="form-text">{{ $help }}</div>@endif
</div>
