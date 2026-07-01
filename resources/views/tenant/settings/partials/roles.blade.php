<div class="card">
    <div class="card-header"><h5 class="mb-0">Roles & Permissions</h5></div>
    <div class="card-body">
        <p class="text-muted mb-3">Manage who can do what in your shop — roles and their permissions are configured on the dedicated screen.</p>
        @can('roles.view')
            <a href="{{ route('tenant.roles.index') }}" class="btn btn-primary">
                <i class="icon-base ti tabler-shield-lock me-1"></i> Manage roles & permissions
            </a>
        @else
            <div class="alert alert-warning mb-0">You don't have permission to manage roles.</div>
        @endcan
    </div>
</div>
