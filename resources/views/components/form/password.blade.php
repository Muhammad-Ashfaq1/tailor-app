{{--
    Central password field: label + input-group-merge with the eye toggle span
    (data-password-toggle is wired globally in js/main.js) + optional feedback.
    Set :feedback="false" for a confirmation field that shows no inline error.

    Usage:
      <x-form.password name="password" id="customer-password" :label="__('customers.app_password')" wrapper="col-md-6 mb-3">
          <x-slot:hint><small class="text-muted">{{ __('customers.app_password_hint') }}</small></x-slot:hint>
      </x-form.password>
      <x-form.password name="password_confirmation" id="customer-password2"
                       :label="__('customers.confirm_password')" :feedback="false" wrapper="col-md-6 mb-3" />
--}}
@props([
    'name',
    'id' => null,
    'label' => null,
    'field' => null,
    'wrapper' => 'mb-3',
    'feedback' => true,
    'requiredMark' => false,
])
@php($id ??= $name)
@php($field ??= $name)
<div class="{{ $wrapper }}">
    @if ($label !== null)
        <label class="form-label" for="{{ $id }}">{{ $label }}{!! $requiredMark ? ' <span class="text-danger">*</span>' : '' !!}@isset($hint) {{ $hint }}@endisset</label>
    @endif
    <div class="input-group input-group-merge{{ $feedback ? ' has-validation' : '' }}">
        <input type="password" id="{{ $id }}" name="{{ $name }}" autocomplete="new-password" {{ $attributes->class('form-control') }}>
        <span class="input-group-text cursor-pointer" data-password-toggle><i class="icon-base ti tabler-eye-off"></i></span>
        @if ($feedback)
            <div class="invalid-feedback" data-field="{{ $field }}"></div>
        @endif
    </div>
</div>
