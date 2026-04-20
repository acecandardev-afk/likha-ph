@props([
    'name' => 'barangay',
    'id' => null,
    'value' => '',
    'required' => false,
    'includeEmpty' => true,
    'emptyLabel' => 'Select barangay',
])

@php
    $id = $id ?? $name;
    $barangays = config('guihulngan.barangays', []);
    $selected = old($name, $value);
@endphp

<select
    name="{{ $name }}"
    id="{{ $id }}"
    {{ $required ? 'required' : '' }}
    {{ $attributes->merge(['class' => 'form-select']) }}
>
    @if($includeEmpty)
        <option value="" @selected($selected === '' || $selected === null)>{{ $emptyLabel }}</option>
    @endif
    @foreach($barangays as $b)
        <option value="{{ $b }}" @selected((string) $selected === $b)>{{ $b }}</option>
    @endforeach
</select>
