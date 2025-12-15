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

<div class="select2-wrapper" data-select2-id="{{ $id }}">
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
        data-coreui-search="true"
        data-coreui-select-all="{{ $showSelectAll ? 'true' : 'false' }}"
        data-coreui-selection-type="tags"
        @endif
        {{ $multiple ? 'multiple' : '' }}
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
                $avatar = is_array($option) ? ($option['avatar'] ?? null) : null;
                $meta = is_array($option) ? ($option['meta'] ?? null) : null;
                $isSelected = in_array($value, (array) $selected);
            @endphp
            <option
                value="{{ $value }}"
                @if($avatar) data-avatar="{{ $avatar }}" @endif
                @if($meta) data-meta="{{ $meta }}" @endif
                {{ $isSelected ? 'selected' : '' }}
            >{{ $text }}</option>
        @endforeach
    </select>

    @error($name)
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

