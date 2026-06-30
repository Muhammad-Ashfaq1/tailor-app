<?php

declare(strict_types=1);

namespace App\Support\Tenancy;

use App\Models\Organization;

/**
 * Single source of truth for "which organization are we acting as right now?".
 *
 * Resolution order:
 *   1. The stancl/tenancy current tenant (set by org.init middleware / Organization::run()).
 *   2. The authenticated user's organization_id (covers the brief window before
 *      org.init runs, queue workers acting as a user, etc.).
 *
 * Returns null for central / super-admin context, in which case the global
 * BelongsToOrganization scope is a no-op and queries span all organizations.
 */
final class OrganizationContext
{
    /** Optional explicit override used by forOrganization() blocks and seeders. */
    private static ?int $override = null;

    public static function id(): ?int
    {
        if (self::$override !== null) {
            return self::$override;
        }

        $tenant = tenant();
        if ($tenant instanceof Organization) {
            return (int) $tenant->getKey();
        }

        $user = auth()->user();
        if ($user !== null && $user->organization_id !== null) {
            return (int) $user->organization_id;
        }

        return null;
    }

    public static function check(): bool
    {
        return self::id() !== null;
    }

    /** Run a callback with the organization context forced to $id (restores after). */
    public static function for(?int $id, callable $callback): mixed
    {
        $previous = self::$override;
        self::$override = $id;

        try {
            return $callback();
        } finally {
            self::$override = $previous;
        }
    }
}
