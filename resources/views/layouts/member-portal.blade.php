<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light-style layout-menu-fixed layout-compact"
      dir="ltr" data-theme="theme-default" data-assets-path="{{ asset('organization/') }}/" data-template="vertical-menu-template-free">
<head>
    @include('layouts.partials.head')
</head>
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            {{-- Focused member menu --}}
            <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
                <div class="app-brand demo">
                    <a href="{{ route('member.dashboard') }}" class="app-brand-link">
                        <span class="app-brand-text demo menu-text fw-bold">{{ config('app.name') }}</span>
                    </a>
                </div>
                <div class="menu-inner-shadow"></div>
                <ul class="menu-inner py-1">
                    <li class="menu-item {{ request()->routeIs('member.dashboard') ? 'active' : '' }}">
                        <a href="{{ route('member.dashboard') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-smart-home"></i><div>Dashboard</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->routeIs('member.reports.*') ? 'active' : '' }}">
                        <a href="{{ route('member.reports.index') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-chart-bar"></i><div>Reports</div>
                        </a>
                    </li>
                </ul>
            </aside>

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
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>

    @include('layouts.partials.scripts')
</body>
</html>
