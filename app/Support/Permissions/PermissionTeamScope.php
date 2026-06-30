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
    /** The explicitly-pinned team, or null when in auto-resolve mode. */
    private static int|string|null $current = null;

    public static function for(int $organizationId, callable $callback): mixed
    {
        $registrar = app(PermissionRegistrar::class);
        $previous = self::$current;

        self::$current = $organizationId;
        $registrar->setPermissionsTeamId($organizationId);

        try {
            return $callback();
        } finally {
            // Restore the parent scope's pin, or null to return to AUTO mode
            // (resolve from tenant()/auth) — never leave a stale explicit team.
            self::$current = $previous;
            $registrar->setPermissionsTeamId($previous);
        }
    }
}
