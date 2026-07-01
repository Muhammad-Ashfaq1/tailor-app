{{-- Shared <head> for the Vuexy admin layouts. Static assets only — no build step. --}}
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', config('app.name')) &middot; {{ config('app.name') }}</title>

@include('layouts.partials.pwa-head')

{{-- Vuexy core theme (copied from the template into public/organization). --}}
<link rel="stylesheet" href="{{ asset('organization/vendor/fonts/iconify-icons.css') }}">
<link rel="stylesheet" href="{{ asset('organization/vendor/css/core.css') }}">
<link rel="stylesheet" href="{{ asset('organization/css/demo.css') }}">
<link rel="stylesheet" href="{{ asset('organization/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}">
{{-- Notyf toast notifications (global — used via window.notyf). --}}
<link rel="stylesheet" href="{{ asset('organization/vendor/libs/notyf/notyf.css') }}">

@stack('vendor-styles')
@stack('styles')

{{-- Per-organization currency config for JS (window.appCurrency). --}}
<script>
    window.appCurrency = @json(\App\Support\Currency::jsConfig());
</script>

{{-- axios + our app bootstrap (plain static files, no bundler). --}}
<script src="{{ asset('organization/libs/axios/axios.min.js') }}"></script>
<script src="{{ asset('organization/js/app.js') }}"></script>

<script src="{{ asset('organization/vendor/js/helpers.js') }}"></script>
<script src="{{ asset('organization/js/config.js') }}"></script>
