{{--
    Central select: label + control + AJAX invalid-feedback. Options are passed
    as the slot so callers keep full control of value/label rendering.

    Usage:
      <x-form.select name="type" id="customer-type" :label="__('customers.type')" wrapper="col-md-6 mb-3">
          @foreach ($types as $type)
              <option value="{{ $type['value'] }}">{{ $type['label'] }}</option>
          @endforeach
      </x-form.select>
--}}
@props([
    'name',
    'id' => null,
    'label' => null,
    'field' => null,
    'wrapper' => 'mb-3',
])
@php($id ??= $name)
@php($field ??= $name)
<div class="{{ $wrapper }}">
    @if ($label !== null)
        <label class="form-label" for="{{ $id }}">{{ $label }}@isset($hint) {{ $hint }}@endisset</label>
    @endif
    <select id="{{ $id }}" name="{{ $name }}" {{ $attributes->class('form-select') }}>{{ $slot }}</select>
    <div class="invalid-feedback" data-field="{{ $field }}"></div>
</div>
