@php $p = $settings['profile']; @endphp

<form class="settings-form card" method="POST" action="{{ route('tenant.settings.save', 'profile') }}">
    @csrf
    <div class="card-body">
        {{-- Shop Information --}}
        <h6 class="text-body mb-3">{{ __('settings.shop_information') }}</h6>
        <div class="row">
            <x-form.input name="shop_name" :label="__('settings.shop_name')" wrapper="col-md-6 mb-3" required-mark value="{{ $organization->name }}" />
            <x-form.input name="business_name" :label="__('settings.business_name')" wrapper="col-md-6 mb-3" required-mark value="{{ $p['business_name'] }}" />
            <x-form.input name="owner_name" :label="__('settings.owner_name')" wrapper="col-md-6 mb-3" required-mark value="{{ $p['owner_name'] }}" />
            <x-form.input name="website_url" :label="__('settings.website_url')" wrapper="col-md-6 mb-3" placeholder="{{ __('settings.website_url_placeholder') }}" value="{{ $p['website_url'] }}" />
        </div>

        <hr class="my-4">

        {{-- Contact Details --}}
        <h6 class="text-body mb-3">{{ __('settings.contact_details') }}</h6>
        <div class="row">
            <x-form.input name="business_email" type="email" :label="__('settings.business_email')" wrapper="col-md-6 mb-3" placeholder="{{ __('settings.business_email_placeholder') }}" value="{{ $p['business_email'] }}" />
            <x-form.input name="business_phone" :label="__('settings.business_phone')" wrapper="col-md-6 mb-3" placeholder="{{ __('settings.business_phone_placeholder') }}" value="{{ $p['business_phone'] }}" />
        </div>

        <hr class="my-4">

        {{-- Business Address --}}
        <h6 class="text-body mb-3">{{ __('settings.business_address') }}</h6>
        <div class="row">
            <x-form.textarea name="address" :label="__('settings.address')" wrapper="col-12 mb-3">{{ $p['address'] }}</x-form.textarea>
            <x-form.input name="city" :label="__('settings.city')" wrapper="col-md-4 mb-3" value="{{ $p['city'] }}" />
            <x-form.input name="state" :label="__('settings.state')" wrapper="col-md-4 mb-3" value="{{ $p['state'] }}" />
            <x-form.input name="country" :label="__('settings.country')" wrapper="col-md-4 mb-3" value="{{ $p['country'] }}" />
        </div>
    </div>
    <div class="card-footer text-end"><button type="submit" class="btn btn-primary">{{ __('settings.save_settings') }}</button></div>
</form>
