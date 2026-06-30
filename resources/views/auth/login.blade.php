@extends('auth.layout')

@section('title', 'Sign in')

@section('content')
    <h4 class="mb-1">Welcome back 👋</h4>
    <p class="mb-4">Sign in to your account.</p>

    <form method="POST" action="{{ route('login.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror"
                   id="email" name="email" value="{{ old('email') }}" autofocus>
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <div class="d-flex justify-content-between">
                <label class="form-label" for="password">Password</label>
                <a href="{{ route('password.request') }}"><small>Forgot password?</small></a>
            </div>
            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
                <label class="form-check-label" for="remember">Remember me</label>
            </div>
        </div>
        <button type="submit" class="btn btn-primary d-grid w-100 mb-3">Sign in</button>
    </form>

    <p class="text-center">
        <span>New here?</span>
        <a href="{{ route('register') }}"><span>Create an organization</span></a>
    </p>
@endsection
