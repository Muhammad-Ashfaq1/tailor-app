@extends('auth.layout')

@section('title', __('auth.login'))

@section('content')
    <h4 class="mb-1">{{ __('auth.login_heading') }}</h4>
    <p class="mb-4">{{ __('auth.login_lead') }}</p>

    <form method="POST" action="{{ route('login.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="email">{{ __('auth.email_address') }}</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror"
                   id="email" name="email" value="{{ old('email') }}" autofocus>
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <div class="d-flex justify-content-between">
                <label class="form-label" for="password">{{ __('auth.password_label') }}</label>
                <a href="{{ route('password.request') }}"><small>{{ __('auth.forgot_password') }}</small></a>
            </div>
            <div class="input-group input-group-merge has-validation">
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                <span class="input-group-text cursor-pointer" data-password-toggle><i class="icon-base ti tabler-eye-off"></i></span>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
                <label class="form-check-label" for="remember">{{ __('auth.remember_me') }}</label>
            </div>
        </div>
        <button type="submit" class="btn btn-primary d-grid w-100 mb-3">{{ __('auth.sign_in') }}</button>
    </form>

    <p class="text-center">
        <span>{{ __('auth.new_here') }}</span>
        <a href="{{ route('register') }}"><span>{{ __('auth.create_organization_link') }}</span></a>
    </p>
@endsection
