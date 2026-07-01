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
            <x-form.input name="prefix" :label="__('settings.prefix')" wrapper="col-md-4 mb-3" required-mark maxlength="10" value="{{ $inv['prefix'] }}" />
            <x-form.input name="next_number" type="number" :label="__('settings.next_number')" wrapper="col-md-4 mb-3" required-mark min="1" value="{{ $inv['next_number'] }}" />
            <x-form.input name="pad_length" type="number" :label="__('settings.pad_length')" wrapper="col-md-4 mb-3" required-mark min="1" max="12" value="{{ $inv['pad_length'] }}">
                <small class="text-muted">e.g. {{ $inv['prefix'] }}{{ str_pad((string) $inv['next_number'], (int) $inv['pad_length'], '0', STR_PAD_LEFT) }}</small>
            </x-form.input>
        </div>

        <hr class="my-4">

        <h6 class="text-body mb-3">{{ __('settings.order_payment') }}</h6>
        <div class="row">
            <x-form.input name="payment_terms_days" type="number" :label="__('settings.payment_terms_days')" wrapper="col-md-6 mb-3" required-mark min="0" max="365" value="{{ $inv['payment_terms_days'] }}" />
            <x-form.input name="tax_rate" type="number" :label="__('settings.tax_rate')" wrapper="col-md-6 mb-3" required-mark step="0.01" min="0" max="100" value="{{ $inv['tax_rate'] }}" />
            <x-form.textarea name="footer_notes" :label="__('settings.footer_notes')" wrapper="col-12 mb-3" rows="3" placeholder="{{ __('settings.footer_notes_placeholder') }}">{{ $inv['footer_notes'] }}</x-form.textarea>
        </div>
    </div>
    <div class="card-footer text-end"><button type="submit" class="btn btn-primary">{{ __('settings.save_settings') }}</button></div>
</form>
