{{--
    Central active/boolean switch. Renders the hidden "0" fallback + the checkbox
    so an unchecked box still posts a value (matches the tenant forms' pattern).

    Usage: <x-form.switch id="customer-active" :label="__('customers.active')" />
--}}
@props([
    'name' => 'is_active',
    'id' => null,
    'label' => null,
    'value' => '1',
    'unchecked' => '0',   // hidden fallback value; pass null to omit
    'checked' => true,
])
@php($id ??= $name)
@if ($unchecked !== null)
    <input type="hidden" name="{{ $name }}" value="{{ $unchecked }}">
@endif
<div class="form-check form-switch">
    <input class="form-check-input" type="checkbox" id="{{ $id }}" name="{{ $name }}" value="{{ $value }}" @checked($checked) {{ $attributes }}>
    <label class="form-check-label" for="{{ $id }}">{{ $label }}</label>
</div>
