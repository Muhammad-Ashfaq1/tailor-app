@extends('auth.layout')

@section('title', 'Verify your email')

@section('content')
    <h4 class="mb-1">Verify your email ✉️</h4>
    <p class="mb-4">
        We sent a verification link to <strong>{{ auth()->user()->email }}</strong>.
        Click it to confirm your address. Your organization will be reviewed for approval.
    </p>

    <form method="POST" action="{{ route('verification.send') }}" class="mb-3">
        @csrf
        <button type="submit" class="btn btn-primary d-grid w-100">Resend verification email</button>
    </form>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-label-secondary d-grid w-100">Sign out</button>
    </form>
@endsection
