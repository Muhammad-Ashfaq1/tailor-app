@php $r = $settings['regional']; @endphp

<form class="settings-form card" method="POST" action="{{ route('tenant.settings.save', 'regional') }}">
    @csrf
    <div class="card-body">
        {{-- Regional --}}
        <h6 class="text-body mb-3">{{ __('settings.regional_heading') }}</h6>
        <div class="row">
            <x-form.select name="locale" :label="__('settings.locale')" wrapper="col-md-6 mb-3" required-mark>
                @foreach ($options['locales'] as $value => $label)
                    <option value="{{ $value }}" @selected($r['locale'] === $value)>{{ $label }}</option>
                @endforeach
            </x-form.select>
            <x-form.select name="timezone" :label="__('settings.timezone')" wrapper="col-md-6 mb-3" required-mark>
                @foreach ($options['timezones'] as $value => $label)
                    <option value="{{ $value }}" @selected($r['timezone'] === $value)>{{ $label }}</option>
                @endforeach
            </x-form.select>
            <x-form.select name="date_format" :label="__('settings.date_format')" wrapper="col-md-4 mb-3" required-mark>
                @foreach ($options['dateFormats'] as $value => $label)
                    <option value="{{ $value }}" @selected($r['date_format'] === $value)>{{ $label }}</option>
                @endforeach
            </x-form.select>
            <x-form.select name="time_format" :label="__('settings.time_format')" wrapper="col-md-4 mb-3" required-mark>
                @foreach ($options['timeFormats'] as $value => $label)
                    <option value="{{ $value }}" @selected($r['time_format'] === $value)>{{ $label }}</option>
                @endforeach
            </x-form.select>
            <x-form.select name="first_day_of_week" :label="__('settings.first_day_of_week')" wrapper="col-md-4 mb-3" required-mark>
                @foreach ($options['firstDaysOfWeek'] as $value => $label)
                    <option value="{{ $value }}" @selected($r['first_day_of_week'] === $value)>{{ $label }}</option>
                @endforeach
            </x-form.select>
        </div>

        <hr class="my-4">

        {{-- Billing / Currency --}}
        <h6 class="text-body mb-3">{{ __('settings.currency_heading') }}</h6>
        <div class="row">
            <x-form.input name="currency" :label="__('settings.currency_code')" wrapper="col-md-3 mb-3" required-mark class="text-uppercase" maxlength="3" value="{{ $r['currency'] }}" />
            <x-form.input name="currency_symbol" :label="__('settings.currency_symbol_short')" wrapper="col-md-3 mb-3" required-mark maxlength="5" value="{{ $r['currency_symbol'] }}" />
            <x-form.select name="currency_position" :label="__('settings.currency_position_short')" wrapper="col-md-3 mb-3" required-mark>
                @foreach ($options['currencyPositions'] as $value => $label)
                    <option value="{{ $value }}" @selected($r['currency_position'] === $value)>{{ $label }}</option>
                @endforeach
            </x-form.select>
            <x-form.input name="currency_decimals" type="number" :label="__('settings.currency_decimals')" wrapper="col-md-3 mb-3" required-mark min="0" max="4" value="{{ $r['currency_decimals'] }}" />
        </div>
    </div>
    <div class="card-footer text-end"><button type="submit" class="btn btn-primary">{{ __('settings.save_settings') }}</button></div>
</form>
