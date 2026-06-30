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

    /**
     * Setting an explicit id pins the team; setting null returns to AUTO mode
     * (resolve from tenant()/auth). PermissionTeamScope relies on this null =
     * auto contract so it can cleanly restore after a scoped block.
     */
    public function setPermissionsTeamId(int|string|Model|null $id): void
    {
        if ($id instanceof Model) {
            $id = $id->getKey();
        }

        $this->teamId = $id;
    }

    public function getPermissionsTeamId(): int|string|null
    {
        if ($this->teamId !== null) {
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
