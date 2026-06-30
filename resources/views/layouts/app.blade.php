<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light-style layout-menu-fixed layout-compact"
      dir="ltr" data-theme="theme-default" data-assets-path="{{ asset('vuexy/') }}/" data-template="vertical-menu-template-free">
<head>
    @include('layouts.partials.head')
</head>
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('layouts.partials.sidebar')

            <div class="layout-page">
                @include('layouts.partials.impersonation-banner')
                <div class="container-fluid p-0">
                    @include('layouts.partials.navbar')
                </div>

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        @include('layouts.partials.flash')
                        @yield('content')
                    </div>

                    <footer class="content-footer footer bg-footer-theme">
                        <div class="container-xxl d-flex flex-wrap justify-content-between py-3">
                            <div>&copy; {{ date('Y') }} {{ config('app.name') }}</div>
                        </div>
                    </footer>

                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>

    @include('layouts.partials.scripts')
</body>
</html>
