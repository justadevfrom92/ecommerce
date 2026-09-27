{{-- $options: [value => label] or [group label => [value => label]] for optgroups. --}}
@props(['name', 'label', 'options' => [], 'value' => null, 'placeholder' => null, 'required' => false, 'help' => null])
@php($id = 'f-'.$name)
@php($current = (string) old($name, $value))
<div {{ $attributes->only('class')->merge(['class' => 'mb-3']) }}>
    <label for="{{ $id }}" class="form-label">{{ $label }}@if ($required) <span class="text-danger" aria-hidden="true">*</span>@endif</label>
    <select id="{{ $id }}" name="{{ $name }}" {{ $attributes->except('class')->class(['form-select', 'is-invalid' => $errors->has($name)]) }} @if ($required) required @endif>
        @if ($placeholder !== null)<option value="">{{ $placeholder }}</option>@endif
        @foreach ($options as $key => $option)
            @if (is_iterable($option))
                <optgroup label="{{ $key }}">
                    @foreach ($option as $v => $l)
                        <option value="{{ $v }}" @selected($current === (string) $v)>{{ $l }}</option>
                    @endforeach
                </optgroup>
            @else
                <option value="{{ $key }}" @selected($current === (string) $key)>{{ $option }}</option>
            @endif
        @endforeach
    </select>
    @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
