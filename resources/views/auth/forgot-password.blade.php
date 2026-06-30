@extends('auth.layout')

@section('title', 'Forgot password')

@section('content')
    <h4 class="mb-1">Forgot password? 🔒</h4>
    <p class="mb-4">Enter your email and we'll send you a reset link.</p>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror"
                   id="email" name="email" value="{{ old('email') }}" autofocus>
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <button type="submit" class="btn btn-primary d-grid w-100 mb-3">Send reset link</button>
    </form>

    <p class="text-center">
        <a href="{{ route('login') }}"><span>Back to sign in</span></a>
    </p>
@endsection
