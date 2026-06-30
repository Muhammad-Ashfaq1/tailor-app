<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Support\Permissions\PermissionCatalog;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeds every permission from the catalog. Permissions are GLOBAL (not
 * team-scoped); only roles are per-organization. Re-runnable via
 * `php artisan permissions:sync`.
 */
class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PermissionCatalog::all() as $name) {
            Permission::findOrCreate($name, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
