{{--
    Central text-style input: label + control + AJAX invalid-feedback slot.
    Preserves the exact Vuexy markup the tenant forms rely on (id, name,
    data-field) so the existing axios/DataTable JS keeps working unchanged.

    Usage:
      <x-form.input name="name" id="customer-name" :label="__('customers.name')"
                    wrapper="col-md-6 mb-3" required />
      <x-form.input name="email" type="email" :label="__('customers.email')">
          <x-slot:hint><small class="text-muted">{{ __('customers.email_hint') }}</small></x-slot:hint>
      </x-form.input>
--}}
@props([
    'name',
    'id' => null,
    'label' => null,
    'type' => 'text',
    'field' => null,        // data-field for inline validation (defaults to name)
    'wrapper' => 'mb-3',    // outer column / spacing classes
    'requiredMark' => false, // show a red * on the label (visual only)
])
@php($id ??= $name)
@php($field ??= $name)
<div class="{{ $wrapper }}">
    @if ($label !== null)
        <label class="form-label" for="{{ $id }}">{{ $label }}{!! $requiredMark ? ' <span class="text-danger">*</span>' : '' !!}@isset($hint) {{ $hint }}@endisset</label>
    @endif
    <input type="{{ $type }}" id="{{ $id }}" name="{{ $name }}" {{ $attributes->class('form-control') }}>
    {{ $slot }}
    <div class="invalid-feedback" data-field="{{ $field }}"></div>
</div>
