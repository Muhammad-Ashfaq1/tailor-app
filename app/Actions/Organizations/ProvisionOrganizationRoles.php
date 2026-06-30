<?php

declare(strict_types=1);

namespace App\Actions\Organizations;

use App\Support\Permissions\PermissionCatalog;
use App\Support\Permissions\PermissionTeamScope;
use Spatie\Permission\Models\Role;

/**
 * Provision the standard tenant roles (tenant_admin, manager, member_lead,
 * member) for a single organization team and wire up the default permission
 * matrix. Idempotent: safe to re-run (used on registration and by seeders).
 */
final class ProvisionOrganizationRoles
{
    public function handle(int $organizationId): void
    {
        PermissionTeamScope::for($organizationId, function () use ($organizationId): void {
            foreach (PermissionCatalog::tenantMatrix() as $roleName => $permissions) {
                $role = Role::firstOrCreate(
                    ['name' => $roleName, 'team_id' => $organizationId, 'guard_name' => 'web'],
                );

                $role->syncPermissions($permissions);
            }
        });
    }
}
