@props([
    'name',
    'id',
    'placeholder',
    'multiple',
    'showSelectAll',
    'required',
    'disabled',
    'label',
    'labelClass',
    'options',
    'selected',
])

<div class="coreui-multi-select-wrapper">
    @if($label)
    <label for="{{ $id }}" class="form-label {{ $labelClass }}">
        {{ $label }}
        @if($required)
        <span class="text-danger">*</span>
        @endif
    </label>
    @endif

    <select
        name="{{ $name }}{{ $multiple ? '[]' : '' }}"
        id="{{ $id }}"
        class="{{ $multiple ? 'form-multi-select' : 'form-select' }} @error($name) is-invalid @enderror"
        @if($multiple)
        multiple
        data-coreui-search="global"
        @if($showSelectAll) data-coreui-select-all="true" @endif
        @endif
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes }}
    >
        @if(!$multiple && !$required)
        <option value=""></option>
        @endif

        {{ $slot }}

        @foreach($options as $option)
            @php
                $value = is_array($option) ? ($option['value'] ?? $option['id'] ?? '') : $option;
                $text = is_array($option) ? ($option['text'] ?? $option['name'] ?? $option['label'] ?? $value) : $option;
                $isSelected = in_array($value, (array) $selected);
            @endphp
            <option
                value="{{ $value }}"
                {{ $isSelected ? 'selected' : '' }}
            >{{ $text }}</option>
        @endforeach
    </select>

    @error($name)
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

