<?php

declare(strict_types=1);

namespace App\Support\Permissions;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Contracts\PermissionsTeamResolver;

/**
 * Resolves spatie's "current team" to the current organization id.
 *
 * Resolution order:
 *   1. An explicitly set team id (PermissionTeamScope / setPermissionsTeamId).
 *   2. The stancl/tenancy current tenant (organization).
 *   3. The authenticated web user's organization_id.
 *   4. 0 — the "central / global" team used for super-admin & shared roles.
 */
final class OrganizationPermissionTeamResolver implements PermissionsTeamResolver
{
    private int|string|null $teamId = null;

    private bool $explicitlySet = false;

    public function setPermissionsTeamId(int|string|Model|null $id): void
    {
        if ($id instanceof Model) {
            $id = $id->getKey();
        }

        $this->teamId = $id;
        $this->explicitlySet = true;
    }

    public function getPermissionsTeamId(): int|string|null
    {
        if ($this->explicitlySet) {
            return $this->teamId;
        }

        $tenant = tenant();
        if ($tenant instanceof Organization) {
            return $tenant->getKey();
        }

        $user = auth()->user();
        if ($user !== null && $user->organization_id !== null) {
            return $user->organization_id;
        }

        return 0;
    }
}
