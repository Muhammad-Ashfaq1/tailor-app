@php $r = $settings['regional']; @endphp

<form class="settings-form card" method="POST" action="{{ route('tenant.settings.save', 'regional') }}">
    @csrf
    <div class="card-body">
        {{-- Regional --}}
        <h6 class="text-body mb-3">Regional</h6>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="locale">Language <span class="text-danger">*</span></label>
                <select class="form-select" id="locale" name="locale">
                    @foreach ($options['locales'] as $value => $label)
                        <option value="{{ $value }}" @selected($r['locale'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback" data-field="locale"></div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="timezone">Timezone <span class="text-danger">*</span></label>
                <select class="form-select" id="timezone" name="timezone">
                    @foreach ($options['timezones'] as $value => $label)
                        <option value="{{ $value }}" @selected($r['timezone'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback" data-field="timezone"></div>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label" for="date_format">Date format <span class="text-danger">*</span></label>
                <select class="form-select" id="date_format" name="date_format">
                    @foreach ($options['dateFormats'] as $value => $label)
                        <option value="{{ $value }}" @selected($r['date_format'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback" data-field="date_format"></div>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label" for="time_format">Time format <span class="text-danger">*</span></label>
                <select class="form-select" id="time_format" name="time_format">
                    @foreach ($options['timeFormats'] as $value => $label)
                        <option value="{{ $value }}" @selected($r['time_format'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback" data-field="time_format"></div>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label" for="first_day_of_week">First day of week <span class="text-danger">*</span></label>
                <select class="form-select" id="first_day_of_week" name="first_day_of_week">
                    @foreach ($options['firstDaysOfWeek'] as $value => $label)
                        <option value="{{ $value }}" @selected($r['first_day_of_week'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback" data-field="first_day_of_week"></div>
            </div>
        </div>

        <hr class="my-4">

        {{-- Billing / Currency --}}
        <h6 class="text-body mb-3">Currency</h6>
        <div class="row">
            <div class="col-md-3 mb-3">
                <label class="form-label" for="currency">Code (ISO) <span class="text-danger">*</span></label>
                <input type="text" class="form-control text-uppercase" id="currency" name="currency" maxlength="3" value="{{ $r['currency'] }}">
                <div class="invalid-feedback" data-field="currency"></div>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label" for="currency_symbol">Symbol <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="currency_symbol" name="currency_symbol" maxlength="5" value="{{ $r['currency_symbol'] }}">
                <div class="invalid-feedback" data-field="currency_symbol"></div>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label" for="currency_position">Symbol position <span class="text-danger">*</span></label>
                <select class="form-select" id="currency_position" name="currency_position">
                    @foreach ($options['currencyPositions'] as $value => $label)
                        <option value="{{ $value }}" @selected($r['currency_position'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback" data-field="currency_position"></div>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label" for="currency_decimals">Decimal places <span class="text-danger">*</span></label>
                <input type="number" min="0" max="4" class="form-control" id="currency_decimals" name="currency_decimals" value="{{ $r['currency_decimals'] }}">
                <div class="invalid-feedback" data-field="currency_decimals"></div>
            </div>
        </div>
    </div>
    <div class="card-footer text-end"><button type="submit" class="btn btn-primary">Save Settings</button></div>
</form>
