<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Support\Permissions\PermissionCatalog;
use App\Support\Permissions\PermissionTeamScope;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeds the GLOBAL (team 0) super_admin role + its permission matrix.
 * Per-organization tenant roles are provisioned by ProvisionOrganizationRoles
 * when an organization is created (and in the demo seeder).
 */
class RolePermissionSeeder extends Seeder
{
    public const GLOBAL_TEAM = 0;

    public function run(): void
    {
        PermissionTeamScope::for(self::GLOBAL_TEAM, function (): void {
            $superAdmin = Role::firstOrCreate(
                ['name' => User::ROLE_SUPER_ADMIN, 'team_id' => self::GLOBAL_TEAM, 'guard_name' => 'web'],
            );

            $superAdmin->syncPermissions(PermissionCatalog::superAdminPermissions());
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
