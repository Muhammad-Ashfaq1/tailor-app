<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light-style layout-wide customizer-hide"
      dir="ltr" data-theme="theme-default" data-assets-path="{{ asset('vuexy/') }}/" data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Account') &middot; {{ config('app.name') }}</title>
    @include('layouts.partials.pwa-head')
    <link rel="stylesheet" href="{{ asset('vuexy/vendor/fonts/iconify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('vuexy/vendor/css/core.css') }}">
    <link rel="stylesheet" href="{{ asset('vuexy/css/demo.css') }}">
    <link rel="stylesheet" href="{{ asset('vuexy/vendor/css/pages/page-auth.css') }}">
    @vite(['resources/js/app.js'])
    <script src="{{ asset('vuexy/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('vuexy/js/config.js') }}"></script>
</head>
<body>
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner">
                <div class="card px-sm-6 px-0">
                    <div class="card-body">
                        <div class="app-brand justify-content-center mb-6">
                            <a href="{{ url('/') }}" class="app-brand-link gap-2">
                                <span class="app-brand-text demo text-heading fw-bold">{{ config('app.name') }}</span>
                            </a>
                        </div>

                        @include('layouts.partials.flash')
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('vuexy/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('vuexy/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('vuexy/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('vuexy/js/main.js') }}"></script>
    @stack('scripts')
</body>
</html>
