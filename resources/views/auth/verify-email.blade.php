@extends('auth.layout')

@section('title', __('auth.verify_email'))

@section('content')
    <h4 class="mb-1">{{ __('auth.verify_heading') }}</h4>
    <p class="mb-4">
        {{ __('auth.verify_lead_before') }} <strong>{{ auth()->user()->email }}</strong>.
        {{ __('auth.verify_lead_after') }}
    </p>

    <form method="POST" action="{{ route('verification.send') }}" class="mb-3">
        @csrf
        <button type="submit" class="btn btn-primary d-grid w-100">{{ __('auth.resend_verification') }}</button>
    </form>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-label-secondary d-grid w-100">{{ __('auth.sign_out') }}</button>
    </form>
@endsection
