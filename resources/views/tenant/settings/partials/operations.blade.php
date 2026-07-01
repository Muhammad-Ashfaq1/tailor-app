@php $o = $settings['operations']; @endphp

<form class="settings-form card" method="POST" action="{{ route('tenant.settings.save', 'operations') }}">
    @csrf
    <div class="card-body">
        <h6 class="text-body mb-3">{{ __('settings.operational_defaults') }}</h6>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="default_stitching_type">{{ __('settings.default_stitching_type') }}</label>
                <input type="text" class="form-control" id="default_stitching_type" name="default_stitching_type"
                       placeholder="{{ __('settings.stitching_type_placeholder') }}" value="{{ $o['default_stitching_type'] }}">
                <div class="invalid-feedback" data-field="default_stitching_type"></div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="measurement_unit">{{ __('settings.measurement_unit') }} <span class="text-danger">*</span></label>
                <select class="form-select" id="measurement_unit" name="measurement_unit">
                    @foreach ($options['measurementUnits'] as $value => $label)
                        <option value="{{ $value }}" @selected($o['measurement_unit'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback" data-field="measurement_unit"></div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="default_delivery_type">{{ __('settings.default_delivery_type') }} <span class="text-danger">*</span></label>
                <select class="form-select" id="default_delivery_type" name="default_delivery_type">
                    @foreach ($options['deliveryTypes'] as $value => $label)
                        <option value="{{ $value }}" @selected($o['default_delivery_type'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback" data-field="default_delivery_type"></div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="home_delivery_charge">{{ __('settings.home_delivery_charge') }}</label>
                <input type="number" step="0.01" min="0" class="form-control" id="home_delivery_charge" name="home_delivery_charge" value="{{ $o['home_delivery_charge'] }}">
                <div class="invalid-feedback" data-field="home_delivery_charge"></div>
            </div>
        </div>
    </div>
    <div class="card-footer text-end"><button type="submit" class="btn btn-primary">{{ __('settings.save_settings') }}</button></div>
</form>
