@php
    $events = $settings['notifications']['events'];
    $loyalty = $settings['loyalty'];
    $creditTypes = ['none' => 'No credit', 'percentage' => 'Percentage', 'fixed' => 'Fixed amount'];
@endphp

{{-- Notifications matrix --}}
<form class="settings-form card mb-4" method="POST" action="{{ route('tenant.settings.notifications.save') }}">
    @csrf
    <div class="card-header">
        <h5 class="mb-0">Notifications</h5>
        <small class="text-muted">Choose how each event notifies you.</small>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-borderless align-middle mb-0">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th class="text-center" style="width:120px;">Email</th>
                        <th class="text-center" style="width:120px;">In-app</th>
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
    <div class="card-footer text-end"><button type="submit" class="btn btn-primary">Save Settings</button></div>
</form>

{{-- Loyalty defaults --}}
<form class="settings-form card" method="POST" action="{{ route('tenant.settings.save', 'loyalty') }}">
    @csrf
    <div class="card-header">
        <h5 class="mb-0">Loyalty</h5>
        <small class="text-muted">Default credit reward applied to new customers (editable per customer).</small>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="default_credit_type">Default credit type <span class="text-danger">*</span></label>
                <select class="form-select" id="default_credit_type" name="default_credit_type">
                    @foreach ($creditTypes as $value => $label)
                        <option value="{{ $value }}" @selected($loyalty['default_credit_type'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback" data-field="default_credit_type"></div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="default_credit_value">Default credit value</label>
                <input type="number" step="0.01" min="0" class="form-control" id="default_credit_value" name="default_credit_value" value="{{ $loyalty['default_credit_value'] }}">
                <div class="invalid-feedback" data-field="default_credit_value"></div>
            </div>
        </div>
    </div>
    <div class="card-footer text-end"><button type="submit" class="btn btn-primary">Save Settings</button></div>
</form>
