{{--
    Central textarea: label + control + AJAX invalid-feedback.
    Usage: <x-form.textarea name="notes" id="customer-notes" :label="__('customers.notes')" rows="2" />
--}}
@props([
    'name',
    'id' => null,
    'label' => null,
    'field' => null,
    'wrapper' => 'mb-3',
    'rows' => 2,
])
@php($id ??= $name)
@php($field ??= $name)
<div class="{{ $wrapper }}">
    @if ($label !== null)
        <label class="form-label" for="{{ $id }}">{{ $label }}@isset($hint) {{ $hint }}@endisset</label>
    @endif
    <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" {{ $attributes->class('form-control') }}>{{ $slot }}</textarea>
    <div class="invalid-feedback" data-field="{{ $field }}"></div>
</div>
