<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Database\Seeders\PermissionSeeder;
use App\Models\Organization;
use App\Actions\Organizations\ProvisionOrganizationRoles;
use Illuminate\Console\Command;

/**
 * Re-run the permission seeder to pick up newly-added permissions from the
 * catalog without a full db re-seed.
 */
class SyncPermissions extends Command
{
    protected $signature = 'permissions:sync';

    protected $description = 'Sync the permission catalog into the database (re-runs PermissionSeeder)';

    public function handle(): int
    {
        $this->info('Syncing permissions from the catalog…');

        $this->callSilent('db:seed', ['--class' => PermissionSeeder::class, '--force' => true]);

        // Existing organizations also need their role permission matrix
        // refreshed; otherwise newly-added sidebar permissions remain absent
        // from roles created before the module was installed.
        Organization::query()->each(function (Organization $organization): void {
            app(ProvisionOrganizationRoles::class)->handle((int) $organization->id);
        });

        $this->info('Permissions synced.');

        return self::SUCCESS;
    }
}
