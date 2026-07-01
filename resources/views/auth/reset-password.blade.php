@extends('auth.layout')

@section('title', __('auth.reset_password'))

@section('content')
    <h4 class="mb-1">{{ __('auth.reset_heading') }}</h4>
    <p class="mb-4">{{ __('auth.reset_lead') }}</p>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="mb-3">
            <label class="form-label" for="email">{{ __('auth.email_address') }}</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror"
                   id="email" name="email" value="{{ old('email', $email) }}" placeholder="{{ __('auth.ph_email') }}">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">{{ __('auth.new_password_label') }}</label>
            <div class="input-group input-group-merge has-validation">
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="{{ __('auth.ph_password') }}">
                <span class="input-group-text cursor-pointer" data-password-toggle><i class="icon-base ti tabler-eye-off"></i></span>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label" for="password_confirmation">{{ __('auth.confirm_password') }}</label>
            <div class="input-group input-group-merge">
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="{{ __('auth.ph_password') }}">
                <span class="input-group-text cursor-pointer" data-password-toggle><i class="icon-base ti tabler-eye-off"></i></span>
            </div>
        </div>
        <button type="submit" class="btn btn-primary d-grid w-100">{{ __('auth.set_new_password') }}</button>
    </form>
@endsection
