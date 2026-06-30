@php($user = auth()->user())
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ url('/') }}" class="app-brand-link">
            <span class="app-brand-text demo menu-text fw-bold">{{ config('app.name') }}</span>
        </a>
    </div>
    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        @if ($user->isSuperAdmin())
            {{-- ---------------- Super-admin menu ---------------- --}}
            <li class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <a href="{{ route('admin.dashboard') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-smart-home"></i>
                    <div>Dashboard</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('admin.organizations.*') ? 'active' : '' }}">
                <a href="{{ route('admin.organizations.index') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-building"></i>
                    <div>Organizations</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('admin.leads.*') ? 'active' : '' }}">
                <a href="{{ route('admin.leads.index') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-user-plus"></i>
                    <div>Leads</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <a href="{{ route('admin.reports.index') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-chart-bar"></i>
                    <div>Reports</div>
                </a>
            </li>
        @else
            {{-- ---------------- Tenant menu ---------------- --}}
            <li class="menu-item {{ request()->routeIs('tenant.dashboard') ? 'active' : '' }}">
                <a href="{{ route('tenant.dashboard') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-smart-home"></i>
                    <div>Dashboard</div>
                </a>
            </li>
            @can('projects.view')
                <li class="menu-item {{ request()->routeIs('tenant.projects.*') ? 'active' : '' }}">
                    <a href="{{ route('tenant.projects.index') }}" class="menu-link">
                        <i class="menu-icon icon-base ti tabler-folders"></i>
                        <div>Projects</div>
                    </a>
                </li>
            @endcan
            @can('tasks.view')
                <li class="menu-item {{ request()->routeIs('tenant.tasks.*') ? 'active' : '' }}">
                    <a href="{{ route('tenant.tasks.index') }}" class="menu-link">
                        <i class="menu-icon icon-base ti tabler-checklist"></i>
                        <div>Tasks</div>
                    </a>
                </li>
            @endcan
            @can('members.view')
                <li class="menu-item {{ request()->routeIs('tenant.members.*') ? 'active' : '' }}">
                    <a href="{{ route('tenant.members.index') }}" class="menu-link">
                        <i class="menu-icon icon-base ti tabler-users"></i>
                        <div>Members</div>
                    </a>
                </li>
            @endcan
            @can('roles.view')
                <li class="menu-item {{ request()->routeIs('tenant.roles.*') ? 'active' : '' }}">
                    <a href="{{ route('tenant.roles.index') }}" class="menu-link">
                        <i class="menu-icon icon-base ti tabler-shield-lock"></i>
                        <div>Roles &amp; Permissions</div>
                    </a>
                </li>
            @endcan
            <li class="menu-item {{ request()->routeIs('tenant.reports.*') ? 'active' : '' }}">
                <a href="{{ route('tenant.reports.index') }}" class="menu-link">
                    <i class="menu-icon icon-base ti tabler-chart-bar"></i>
                    <div>Reports</div>
                </a>
            </li>
            @can('settings.manage')
                <li class="menu-item {{ request()->routeIs('tenant.settings.*') ? 'active' : '' }}">
                    <a href="{{ route('tenant.settings.index') }}" class="menu-link">
                        <i class="menu-icon icon-base ti tabler-settings"></i>
                        <div>Settings</div>
                    </a>
                </li>
            @endcan
        @endif
    </ul>
</aside>
