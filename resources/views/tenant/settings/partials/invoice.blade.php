@php $inv = $settings['invoice']; $r = $settings['regional']; @endphp

<form class="settings-form card" method="POST" action="{{ route('tenant.settings.save', 'invoice') }}">
    @csrf
    <div class="card-body">
        <div class="alert alert-primary" role="alert">
            <i class="icon-base ti tabler-info-circle me-1"></i>
            {{ __('settings.invoice_currency_notice_before') }}
            <a href="{{ route('tenant.settings.index', 'regional') }}" class="alert-link">{{ __('settings.tabs.regional') }}</a>
            ({{ __('settings.invoice_currency_notice_after') }} <strong>{{ $r['currency'] }}</strong>).
        </div>

        <h6 class="text-body mb-3">{{ __('settings.invoice_numbering') }}</h6>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label" for="prefix">{{ __('settings.prefix') }} <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="prefix" name="prefix" maxlength="10" value="{{ $inv['prefix'] }}">
                <div class="invalid-feedback" data-field="prefix"></div>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label" for="next_number">{{ __('settings.next_number') }} <span class="text-danger">*</span></label>
                <input type="number" min="1" class="form-control" id="next_number" name="next_number" value="{{ $inv['next_number'] }}">
                <div class="invalid-feedback" data-field="next_number"></div>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label" for="pad_length">{{ __('settings.pad_length') }} <span class="text-danger">*</span></label>
                <input type="number" min="1" max="12" class="form-control" id="pad_length" name="pad_length" value="{{ $inv['pad_length'] }}">
                <small class="text-muted">e.g. {{ $inv['prefix'] }}{{ str_pad((string) $inv['next_number'], (int) $inv['pad_length'], '0', STR_PAD_LEFT) }}</small>
                <div class="invalid-feedback" data-field="pad_length"></div>
            </div>
        </div>

        <hr class="my-4">

        <h6 class="text-body mb-3">{{ __('settings.order_payment') }}</h6>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="payment_terms_days">{{ __('settings.payment_terms_days') }} <span class="text-danger">*</span></label>
                <input type="number" min="0" max="365" class="form-control" id="payment_terms_days" name="payment_terms_days" value="{{ $inv['payment_terms_days'] }}">
                <div class="invalid-feedback" data-field="payment_terms_days"></div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="tax_rate">{{ __('settings.tax_rate') }} <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0" max="100" class="form-control" id="tax_rate" name="tax_rate" value="{{ $inv['tax_rate'] }}">
                <div class="invalid-feedback" data-field="tax_rate"></div>
            </div>
            <div class="col-12 mb-3">
                <label class="form-label" for="footer_notes">{{ __('settings.footer_notes') }}</label>
                <textarea class="form-control" id="footer_notes" name="footer_notes" rows="3" placeholder="{{ __('settings.footer_notes_placeholder') }}">{{ $inv['footer_notes'] }}</textarea>
                <div class="invalid-feedback" data-field="footer_notes"></div>
            </div>
        </div>
    </div>
    <div class="card-footer text-end"><button type="submit" class="btn btn-primary">{{ __('settings.save_settings') }}</button></div>
</form>
