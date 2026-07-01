@php $p = $settings['profile']; @endphp

<form class="settings-form card" method="POST" action="{{ route('tenant.settings.save', 'profile') }}">
    @csrf
    <div class="card-body">
        {{-- Shop Information --}}
        <h6 class="text-body mb-3">Shop Information</h6>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="shop_name">Shop Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="shop_name" name="shop_name" value="{{ $organization->name }}">
                <div class="invalid-feedback" data-field="shop_name"></div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="business_name">Business Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="business_name" name="business_name" value="{{ $p['business_name'] }}">
                <div class="invalid-feedback" data-field="business_name"></div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="owner_name">Owner Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="owner_name" name="owner_name" value="{{ $p['owner_name'] }}">
                <div class="invalid-feedback" data-field="owner_name"></div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="website_url">Website URL</label>
                <input type="text" class="form-control" id="website_url" name="website_url" placeholder="https://…" value="{{ $p['website_url'] }}">
                <div class="invalid-feedback" data-field="website_url"></div>
            </div>
        </div>

        <hr class="my-4">

        {{-- Contact Details --}}
        <h6 class="text-body mb-3">Contact Details</h6>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="business_email">Business Email</label>
                <input type="email" class="form-control" id="business_email" name="business_email" value="{{ $p['business_email'] }}">
                <div class="invalid-feedback" data-field="business_email"></div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="business_phone">Business Phone</label>
                <input type="text" class="form-control" id="business_phone" name="business_phone" value="{{ $p['business_phone'] }}">
                <div class="invalid-feedback" data-field="business_phone"></div>
            </div>
        </div>

        <hr class="my-4">

        {{-- Business Address --}}
        <h6 class="text-body mb-3">Business Address</h6>
        <div class="row">
            <div class="col-12 mb-3">
                <label class="form-label" for="address">Address</label>
                <textarea class="form-control" id="address" name="address" rows="2">{{ $p['address'] }}</textarea>
                <div class="invalid-feedback" data-field="address"></div>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label" for="city">City</label>
                <input type="text" class="form-control" id="city" name="city" value="{{ $p['city'] }}">
                <div class="invalid-feedback" data-field="city"></div>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label" for="state">State</label>
                <input type="text" class="form-control" id="state" name="state" value="{{ $p['state'] }}">
                <div class="invalid-feedback" data-field="state"></div>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label" for="country">Country</label>
                <input type="text" class="form-control" id="country" name="country" value="{{ $p['country'] }}">
                <div class="invalid-feedback" data-field="country"></div>
            </div>
        </div>
    </div>
    <div class="card-footer text-end"><button type="submit" class="btn btn-primary">Save Settings</button></div>
</form>
