@php($user = auth()->user())
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ url('/') }}" class="app-brand-link">
            <span class="app-brand-text demo menu-text fw-bold">{{ config('app.name') }}</span>
        </a>
    </div>
    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1 d-flex flex-column">
        @if ($user->isSuperAdmin())
            {{-- ---------------- Super-admin menu ---------------- --}}
            <li class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <a href="{{ route('admin.dashboard') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-smart-home"></i>
                    <div>{{ __('menu.dashboard') }}</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('admin.organizations.*') ? 'active' : '' }}">
                <a href="{{ route('admin.organizations.index') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-building"></i>
                    <div>{{ __('menu.organizations') }}</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('admin.leads.*') ? 'active' : '' }}">
                <a href="{{ route('admin.leads.index') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-user-plus"></i>
                    <div>{{ __('menu.leads') }}</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <a href="{{ route('admin.reports.index') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-chart-bar"></i>
                    <div>{{ __('menu.reports') }}</div>
                </a>
            </li>
        @else
            {{-- ---------------- Tenant menu ---------------- --}}
            <li class="menu-item {{ request()->routeIs('tenant.dashboard') ? 'active' : '' }}">
                <a href="{{ route('tenant.dashboard') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-smart-home"></i>
                    <div>{{ __('menu.dashboard') }}</div>
                </a>
            </li>
            @can('customers.view')
                <li class="menu-item {{ request()->routeIs('tenant.customers.*') ? 'active' : '' }}">
                    <a href="{{ route('tenant.customers.index') }}" class="menu-link">
                        <i class="menu-icon icon-base ti tabler-user-heart"></i>
                        <div>{{ __('menu.customers') }}</div>
                    </a>
                </li>
            @endcan
            @can('members.view')
                <li class="menu-item {{ request()->routeIs('tenant.members.*') ? 'active' : '' }}">
                    <a href="{{ route('tenant.members.index') }}" class="menu-link">
                        <i class="menu-icon icon-base ti tabler-users"></i>
                        <div>{{ __('menu.members') }}</div>
                    </a>
                </li>
            @endcan
            @can('roles.view')
                <li class="menu-item {{ request()->routeIs('tenant.roles.*') ? 'active' : '' }}">
                    <a href="{{ route('tenant.roles.index') }}" class="menu-link">
                        <i class="menu-icon icon-base ti tabler-shield-lock"></i>
                        <div>{{ __('menu.roles') }}</div>
                    </a>
                </li>
            @endcan
            <li class="menu-item {{ request()->routeIs('tenant.reports.*') ? 'active' : '' }}">
                <a href="{{ route('tenant.reports.index') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-chart-bar"></i>
                    <div>{{ __('menu.reports') }}</div>
                </a>
            </li>
            @can('settings.manage')
                {{-- Pinned to the very bottom of the sidebar. --}}
                <li class="menu-item mt-auto {{ request()->routeIs('tenant.settings.*') ? 'active' : '' }}">
                    <a href="{{ route('tenant.settings.index') }}" class="menu-link">
                        <i class="menu-icon icon-base ti tabler-settings"></i>
                        <div>{{ __('menu.settings') }}</div>
                    </a>
                </li>
            @endcan
        @endif
    </ul>
</aside>
