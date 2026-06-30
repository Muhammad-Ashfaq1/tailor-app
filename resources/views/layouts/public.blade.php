<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name')) &middot; {{ config('app.name') }}</title>
    <meta name="description" content="@yield('meta-description', 'The multi-tenant platform for modern teams.')">

    @include('layouts.partials.pwa-head')

    {{-- Landing design tokens. Namespaced *-landing classes keep this CSS from
         leaking into the Vuexy admin theme. --}}
    <style>
        :root {
            --landing-primary: #7367f0;
            --landing-primary-dark: #5e54d6;
            --landing-ink: #2f2b3d;
            --landing-muted: #6f6b7d;
            --landing-bg: #ffffff;
            --landing-bg-alt: #f6f6fb;
            --landing-radius: 14px;
            --landing-max: 1140px;
        }
        * { box-sizing: border-box; }
        body.page-landing { margin: 0; font-family: 'Instrument Sans', system-ui, sans-serif; color: var(--landing-ink); background: var(--landing-bg); }
        .landing-container { max-width: var(--landing-max); margin: 0 auto; padding: 0 1.25rem; }
        .landing-nav { display: flex; align-items: center; justify-content: space-between; padding: 1.25rem 0; }
        .landing-brand { font-weight: 700; font-size: 1.35rem; color: var(--landing-primary); text-decoration: none; }
        .landing-btn { display: inline-block; padding: .65rem 1.25rem; border-radius: var(--landing-radius); font-weight: 600; text-decoration: none; cursor: pointer; border: 0; }
        .landing-btn--primary { background: var(--landing-primary); color: #fff; }
        .landing-btn--primary:hover { background: var(--landing-primary-dark); }
        .landing-btn--ghost { background: transparent; color: var(--landing-ink); }
        .landing-hero { padding: 4rem 0; text-align: center; }
        .landing-hero h1 { font-size: clamp(2rem, 5vw, 3.25rem); margin: 0 0 1rem; }
        .landing-hero p { color: var(--landing-muted); font-size: 1.15rem; max-width: 640px; margin: 0 auto 2rem; }
        .landing-section { padding: 3.5rem 0; }
        .landing-section--alt { background: var(--landing-bg-alt); }
        .landing-grid { display: grid; gap: 1.5rem; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); }
        .landing-card { background: #fff; border: 1px solid #eee; border-radius: var(--landing-radius); padding: 1.75rem; }
        .landing-card h3 { margin: .25rem 0 .5rem; }
        .landing-card p { color: var(--landing-muted); margin: 0; }
        .landing-footer { padding: 2rem 0; border-top: 1px solid #eee; color: var(--landing-muted); text-align: center; }
        .landing-modal { position: fixed; inset: 0; background: rgba(0,0,0,.5); display: none; align-items: center; justify-content: center; z-index: 50; }
        .landing-modal.is-open { display: flex; }
        .landing-modal__panel { background: #fff; border-radius: var(--landing-radius); padding: 2rem; width: min(460px, 92vw); }
        .landing-field { display: block; width: 100%; padding: .65rem .85rem; margin-bottom: .85rem; border: 1px solid #ddd; border-radius: 10px; font: inherit; }
        .landing-alert { padding: .65rem .85rem; border-radius: 10px; margin-bottom: 1rem; }
        .landing-alert--success { background: #e8f8ef; color: #1f9254; }
        .landing-alert--error { background: #fdeaea; color: #c0392b; }
    </style>

    @vite(['resources/js/app.js'])
</head>
<body class="page-landing">
    <header class="landing-container">
        <nav class="landing-nav">
            <a href="{{ url('/') }}" class="landing-brand">{{ config('app.name') }}</a>
            <div>
                <a href="{{ route('login') }}" class="landing-btn landing-btn--ghost">Sign in</a>
                <a href="{{ route('register') }}" class="landing-btn landing-btn--primary">Get started</a>
            </div>
        </nav>
    </header>

    @yield('content')

    <footer class="landing-footer">
        <div class="landing-container">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</div>
    </footer>

    @stack('scripts')
</body>
</html>
