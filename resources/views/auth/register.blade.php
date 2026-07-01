@extends('auth.layout')

@section('title', __('auth.register_page_title'))

@section('content')
    <h4 class="mb-1">{{ __('auth.register_heading') }}</h4>
    <p class="mb-4">{{ __('auth.register_lead') }}</p>

    <form method="POST" action="{{ route('register.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="organization_name">{{ __('auth.org_name_label') }}</label>
            <input type="text" class="form-control @error('organization_name') is-invalid @enderror"
                   id="organization_name" name="organization_name" value="{{ old('organization_name') }}" autofocus>
            @error('organization_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="name">{{ __('auth.your_name_label') }}</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror"
                   id="name" name="name" value="{{ old('name') }}">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="email">{{ __('auth.email_address') }}</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror"
                   id="email" name="email" value="{{ old('email') }}">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">{{ __('auth.password_label') }}</label>
            <div class="input-group input-group-merge has-validation">
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                <span class="input-group-text cursor-pointer" data-password-toggle><i class="icon-base ti tabler-eye-off"></i></span>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label" for="password_confirmation">{{ __('auth.confirm_password') }}</label>
            <div class="input-group input-group-merge">
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                <span class="input-group-text cursor-pointer" data-password-toggle><i class="icon-base ti tabler-eye-off"></i></span>
            </div>
        </div>
        <button type="submit" class="btn btn-primary d-grid w-100 mb-3">{{ __('auth.create_organization_button') }}</button>
    </form>

    <p class="text-center">
        <span>{{ __('auth.already_have_account') }}</span>
        <a href="{{ route('login') }}"><span>{{ __('auth.sign_in_instead') }}</span></a>
    </p>
@endsection
