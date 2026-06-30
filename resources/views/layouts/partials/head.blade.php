{{-- Shared <head> for the Vuexy admin layouts. --}}
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', config('app.name')) &middot; {{ config('app.name') }}</title>

@include('layouts.partials.pwa-head')

{{-- Vuexy core theme (compiled, bundled under resources/views/vuexy-template/assets) --}}
<link rel="stylesheet" href="{{ asset('vuexy/vendor/fonts/iconify-icons.css') }}">
<link rel="stylesheet" href="{{ asset('vuexy/vendor/css/core.css') }}">
<link rel="stylesheet" href="{{ asset('vuexy/css/demo.css') }}">
<link rel="stylesheet" href="{{ asset('vuexy/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}">

@stack('vendor-styles')
@stack('styles')

{{-- Per-organization currency config for JS (window.appCurrency). --}}
<script>
    window.appCurrency = @json(\App\Support\Currency::jsConfig());
</script>

{{-- Vite: our axios + currency bootstrap. --}}
@vite(['resources/js/app.js'])

<script src="{{ asset('vuexy/vendor/js/helpers.js') }}"></script>
<script src="{{ asset('vuexy/vendor/js/template-customizer.js') }}"></script>
<script src="{{ asset('vuexy/js/config.js') }}"></script>
