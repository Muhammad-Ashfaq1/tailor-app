@extends('auth.layout')

@section('title', __('auth.forgot_page_title'))

@section('content')
    <h4 class="mb-1">{{ __('auth.forgot_heading') }}</h4>
    <p class="mb-4">{{ __('auth.forgot_lead') }}</p>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="email">{{ __('auth.email_address') }}</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror"
                   id="email" name="email" value="{{ old('email') }}" autofocus>
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <button type="submit" class="btn btn-primary d-grid w-100 mb-3">{{ __('auth.send_reset_link_button') }}</button>
    </form>

    <p class="text-center">
        <a href="{{ route('login') }}"><span>{{ __('auth.back_to_sign_in') }}</span></a>
    </p>
@endsection
