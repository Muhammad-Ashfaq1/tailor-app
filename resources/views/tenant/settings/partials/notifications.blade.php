@php
    $events = $settings['notifications']['events'];
    $loyalty = $settings['loyalty'];
    $creditTypes = ['none' => __('settings.credit_type_none'), 'percentage' => __('settings.credit_type_percentage'), 'fixed' => __('settings.credit_type_fixed')];
@endphp

{{-- Notifications matrix --}}
<form class="settings-form card mb-4" method="POST" action="{{ route('tenant.settings.notifications.save') }}">
    @csrf
    <div class="card-header">
        <h5 class="mb-0">{{ __('settings.notifications_heading') }}</h5>
        <small class="text-muted">{{ __('settings.notifications_help') }}</small>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-borderless align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ __('settings.event') }}</th>
                        <th class="text-center" style="width:120px;">{{ __('settings.channel_email') }}</th>
                        <th class="text-center" style="width:120px;">{{ __('settings.channel_in_app') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($notificationEvents as $key => $label)
                        @php $event = $events[$key] ?? ['email' => false, 'in_app' => false]; @endphp
                        <tr>
                            <td>{{ $label }}</td>
                            <td class="text-center">
                                <input type="hidden" name="events[{{ $key }}][email]" value="0">
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input" type="checkbox" name="events[{{ $key }}][email]" value="1" @checked($event['email'])>
                                </div>
                            </td>
                            <td class="text-center">
                                <input type="hidden" name="events[{{ $key }}][in_app]" value="0">
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input" type="checkbox" name="events[{{ $key }}][in_app]" value="1" @checked($event['in_app'])>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer text-end"><button type="submit" class="btn btn-primary">{{ __('settings.save_settings') }}</button></div>
</form>

{{-- Loyalty defaults --}}
<form class="settings-form card" method="POST" action="{{ route('tenant.settings.save', 'loyalty') }}">
    @csrf
    <div class="card-header">
        <h5 class="mb-0">{{ __('settings.loyalty_heading') }}</h5>
        <small class="text-muted">{{ __('settings.loyalty_help') }}</small>
    </div>
    <div class="card-body">
        <div class="row">
            <x-form.select name="default_credit_type" :label="__('settings.default_credit_type')" wrapper="col-md-6 mb-3" required-mark>
                @foreach ($creditTypes as $value => $label)
                    <option value="{{ $value }}" @selected($loyalty['default_credit_type'] === $value)>{{ $label }}</option>
                @endforeach
            </x-form.select>
            <x-form.input name="default_credit_value" type="number" :label="__('settings.default_credit_value')" wrapper="col-md-6 mb-3" step="0.01" min="0" value="{{ $loyalty['default_credit_value'] }}" />
        </div>
    </div>
    <div class="card-footer text-end"><button type="submit" class="btn btn-primary">{{ __('settings.save_settings') }}</button></div>
</form>
