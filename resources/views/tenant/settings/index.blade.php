@extends('layouts.app')

@section('title', 'Settings')
@section('page-title', 'Settings')

@php
    use Illuminate\Support\Str;
    $reg = $settings['regional'];
    $tax = $settings['tax'];
    $inv = $settings['invoice'];
    $notif = $settings['notifications'];
@endphp

@section('content')
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="list-group">
            @foreach ($sections as $s)
                <a href="{{ route('tenant.settings.index', $s) }}"
                   class="list-group-item list-group-item-action {{ $s === $section ? 'active' : '' }}">
                    {{ Str::headline($s) }}
                </a>
            @endforeach
        </div>
    </div>

    <div class="col-md-9">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">{{ Str::headline($section) }}</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('tenant.settings.save', $section) }}">
                    @csrf

                    @if ($section === 'general')
                        <div class="mb-3">
                            <label class="form-label" for="name">Organization name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $organization->name) }}">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="date_format">Date format</label>
                            <input type="text" class="form-control" id="date_format" name="date_format"
                                   value="{{ old('date_format', $reg['date_format']) }}">
                        </div>

                    @elseif ($section === 'regional')
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="currency">Currency (ISO)</label>
                                <input type="text" class="form-control" id="currency" name="currency" value="{{ old('currency', $reg['currency']) }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="currency_symbol">Symbol</label>
                                <input type="text" class="form-control" id="currency_symbol" name="currency_symbol" value="{{ old('currency_symbol', $reg['currency_symbol']) }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="currency_position">Symbol position</label>
                                <select class="form-select" id="currency_position" name="currency_position">
                                    <option value="before" @selected($reg['currency_position'] === 'before')>Before ($100)</option>
                                    <option value="after" @selected($reg['currency_position'] === 'after')>After (100$)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="timezone">Timezone</label>
                                <input type="text" class="form-control" id="timezone" name="timezone" value="{{ old('timezone', $reg['timezone']) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="locale">Locale</label>
                                <input type="text" class="form-control" id="locale" name="locale" value="{{ old('locale', $reg['locale']) }}">
                            </div>
                        </div>

                    @elseif ($section === 'operations')
                        <h6>Tax</h6>
                        <input type="hidden" name="tax_enabled" value="0">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="tax_enabled" name="tax_enabled" value="1" @checked($tax['enabled'])>
                            <label class="form-check-label" for="tax_enabled">Enable tax</label>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="tax_rate">Tax rate (%)</label>
                                <input type="number" step="0.01" class="form-control" id="tax_rate" name="tax_rate" value="{{ old('tax_rate', $tax['rate']) }}">
                            </div>
                            <div class="col-md-6 mb-3 d-flex align-items-end">
                                <input type="hidden" name="tax_inclusive" value="0">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="tax_inclusive" name="tax_inclusive" value="1" @checked($tax['inclusive'])>
                                    <label class="form-check-label" for="tax_inclusive">Prices include tax</label>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <h6>Invoicing</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="invoice_prefix">Prefix</label>
                                <input type="text" class="form-control" id="invoice_prefix" name="invoice_prefix" value="{{ old('invoice_prefix', $inv['prefix']) }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="invoice_next_number">Next number</label>
                                <input type="number" class="form-control" id="invoice_next_number" name="invoice_next_number" value="{{ old('invoice_next_number', $inv['next_number']) }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="invoice_pad_length">Pad length</label>
                                <input type="number" class="form-control" id="invoice_pad_length" name="invoice_pad_length" value="{{ old('invoice_pad_length', $inv['pad_length']) }}">
                            </div>
                        </div>

                    @elseif ($section === 'notifications')
                        @foreach (['email_enabled' => 'Email notifications', 'notify_on_new_member' => 'Notify on new member', 'notify_on_status_change' => 'Notify on status change'] as $field => $label)
                            <input type="hidden" name="{{ $field }}" value="0">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="{{ $field }}" name="{{ $field }}" value="1" @checked($notif[$field])>
                                <label class="form-check-label" for="{{ $field }}">{{ $label }}</label>
                            </div>
                        @endforeach
                    @endif

                    <button type="submit" class="btn btn-primary">Save changes</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
