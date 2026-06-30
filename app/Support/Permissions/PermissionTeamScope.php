<?php

declare(strict_types=1);

namespace App\Support\Permissions;

use Spatie\Permission\PermissionRegistrar;

/**
 * Run a callback with spatie's "current team" pinned to a specific
 * organization id, then restore the previous team. Used whenever we read or
 * write role/permission pivots for an organization other than the ambient one
 * (e.g. seeding, super-admin editing a tenant, keeping users.role in sync).
 */
final class PermissionTeamScope
{
    public static function for(int $organizationId, callable $callback): mixed
    {
        $registrar = app(PermissionRegistrar::class);
        $previous = $registrar->getPermissionsTeamId();

        $registrar->setPermissionsTeamId($organizationId);

        try {
            return $callback();
        } finally {
            $registrar->setPermissionsTeamId($previous);
        }
    }
}
