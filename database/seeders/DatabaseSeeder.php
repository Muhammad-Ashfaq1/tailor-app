<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Support\Permissions\PermissionTeamScope;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Permissions + the global super-admin role.
        $this->call([
            PermissionSeeder::class,
            RolePermissionSeeder::class,
        ]);

        // 2. The platform super admin (organization_id = null, team 0).
        $superAdmin = User::query()->firstOrCreate(
            ['email' => 'super@admin.test'],
            [
                'organization_id' => null,
                'name' => 'Super Admin',
                'password' => 'password',
                'role' => User::ROLE_SUPER_ADMIN,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        PermissionTeamScope::for(RolePermissionSeeder::GLOBAL_TEAM, function () use ($superAdmin): void {
            $superAdmin->syncRoles([User::ROLE_SUPER_ADMIN]);
        });

        // 3. Demo tenant with full data.
        $this->call(DemoOrganizationSeeder::class);
    }
}
