@php $o = $settings['operations']; @endphp

<form class="settings-form card" method="POST" action="{{ route('tenant.settings.save', 'operations') }}">
    @csrf
    <div class="card-body">
        <h6 class="text-body mb-3">{{ __('settings.operational_defaults') }}</h6>
        <div class="row">
            <x-form.input name="default_stitching_type" :label="__('settings.default_stitching_type')" wrapper="col-md-6 mb-3" placeholder="{{ __('settings.stitching_type_placeholder') }}" value="{{ $o['default_stitching_type'] }}" />
            <x-form.select name="measurement_unit" :label="__('settings.measurement_unit')" wrapper="col-md-6 mb-3" required-mark>
                @foreach ($options['measurementUnits'] as $value => $label)
                    <option value="{{ $value }}" @selected($o['measurement_unit'] === $value)>{{ $label }}</option>
                @endforeach
            </x-form.select>
            <x-form.select name="default_delivery_type" :label="__('settings.default_delivery_type')" wrapper="col-md-6 mb-3" required-mark>
                @foreach ($options['deliveryTypes'] as $value => $label)
                    <option value="{{ $value }}" @selected($o['default_delivery_type'] === $value)>{{ $label }}</option>
                @endforeach
            </x-form.select>
            <x-form.input name="home_delivery_charge" type="number" :label="__('settings.home_delivery_charge')" wrapper="col-md-6 mb-3" step="0.01" min="0" value="{{ $o['home_delivery_charge'] }}" />
        </div>
    </div>
    <div class="card-footer text-end"><button type="submit" class="btn btn-primary">{{ __('settings.save_settings') }}</button></div>
</form>
