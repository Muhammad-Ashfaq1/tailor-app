<div class="card">
    <div class="card-header"><h5 class="mb-0">{{ __('settings.tabs.roles') }}</h5></div>
    <div class="card-body">
        <p class="text-muted mb-3">{{ __('settings.roles_description') }}</p>
        @can('roles.view')
            <a href="{{ route('tenant.roles.index') }}" class="btn btn-primary">
                <i class="icon-base ti tabler-shield-lock me-1"></i> {{ __('settings.roles_manage_button') }}
            </a>
        @else
            <div class="alert alert-warning mb-0">{{ __('settings.roles_no_permission') }}</div>
        @endcan
    </div>
</div>
