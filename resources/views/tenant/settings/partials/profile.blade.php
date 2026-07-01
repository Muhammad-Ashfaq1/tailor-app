@php $p = $settings['profile']; @endphp

<form class="settings-form card" method="POST" action="{{ route('tenant.settings.save', 'profile') }}">
    @csrf
    <div class="card-body">
        {{-- Shop Information --}}
        <h6 class="text-body mb-3">{{ __('settings.shop_information') }}</h6>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="shop_name">{{ __('settings.shop_name') }} <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="shop_name" name="shop_name" value="{{ $organization->name }}">
                <div class="invalid-feedback" data-field="shop_name"></div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="business_name">{{ __('settings.business_name') }} <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="business_name" name="business_name" value="{{ $p['business_name'] }}">
                <div class="invalid-feedback" data-field="business_name"></div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="owner_name">{{ __('settings.owner_name') }} <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="owner_name" name="owner_name" value="{{ $p['owner_name'] }}">
                <div class="invalid-feedback" data-field="owner_name"></div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="website_url">{{ __('settings.website_url') }}</label>
                <input type="text" class="form-control" id="website_url" name="website_url" placeholder="{{ __('settings.website_url_placeholder') }}" value="{{ $p['website_url'] }}">
                <div class="invalid-feedback" data-field="website_url"></div>
            </div>
        </div>

        <hr class="my-4">

        {{-- Contact Details --}}
        <h6 class="text-body mb-3">{{ __('settings.contact_details') }}</h6>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="business_email">{{ __('settings.business_email') }}</label>
                <input type="email" class="form-control" id="business_email" name="business_email" value="{{ $p['business_email'] }}">
                <div class="invalid-feedback" data-field="business_email"></div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="business_phone">{{ __('settings.business_phone') }}</label>
                <input type="text" class="form-control" id="business_phone" name="business_phone" value="{{ $p['business_phone'] }}">
                <div class="invalid-feedback" data-field="business_phone"></div>
            </div>
        </div>

        <hr class="my-4">

        {{-- Business Address --}}
        <h6 class="text-body mb-3">{{ __('settings.business_address') }}</h6>
        <div class="row">
            <div class="col-12 mb-3">
                <label class="form-label" for="address">{{ __('settings.address') }}</label>
                <textarea class="form-control" id="address" name="address" rows="2">{{ $p['address'] }}</textarea>
                <div class="invalid-feedback" data-field="address"></div>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label" for="city">{{ __('settings.city') }}</label>
                <input type="text" class="form-control" id="city" name="city" value="{{ $p['city'] }}">
                <div class="invalid-feedback" data-field="city"></div>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label" for="state">{{ __('settings.state') }}</label>
                <input type="text" class="form-control" id="state" name="state" value="{{ $p['state'] }}">
                <div class="invalid-feedback" data-field="state"></div>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label" for="country">{{ __('settings.country') }}</label>
                <input type="text" class="form-control" id="country" name="country" value="{{ $p['country'] }}">
                <div class="invalid-feedback" data-field="country"></div>
            </div>
        </div>
    </div>
    <div class="card-footer text-end"><button type="submit" class="btn btn-primary">{{ __('settings.save_settings') }}</button></div>
</form>
