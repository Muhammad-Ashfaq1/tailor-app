<?php

declare(strict_types=1);

namespace App\Support\Permissions;

use App\Models\User;

/**
 * Single source of truth for every permission in the system and the default
 * role -> permission matrix. The seeders, the permissions:sync command and the
 * per-organization role provisioning all read from here.
 */
final class PermissionCatalog
{
    /**
     * Resource => list of granular abilities. An aggregate "<resource>.manage"
     * is added automatically for every resource.
     *
     * @var array<string, array<int, string>>
     */
    private const RESOURCES = [
        // Tenant resources.
        'members' => ['view', 'create', 'update', 'delete', 'impersonate'],
        'roles' => ['view'],
        'settings' => [],
        'reports' => ['view'],

        // Central / super-admin resources.
        'organizations' => ['view', 'create', 'update', 'delete'],
        'leads' => ['view', 'update'],
    ];

    /** @return array<int, string> Flat list of every permission name. */
    public static function all(): array
    {
        $permissions = [];

        foreach (self::RESOURCES as $resource => $abilities) {
            foreach ($abilities as $ability) {
                $permissions[] = "{$resource}.{$ability}";
            }
            // Every resource gets an aggregate "manage".
            $permissions[] = "{$resource}.manage";
        }

        return array_values(array_unique($permissions));
    }

    /**
     * Default role => permissions matrix for a TENANT team. '*' on a resource
     * expands to all of that resource's permissions (granular + manage).
     *
     * @return array<string, array<int, string>>
     */
    public static function tenantMatrix(): array
    {
        return [
            User::ROLE_TENANT_ADMIN => self::expand([
                'members' => '*', 'roles' => '*', 'settings' => '*', 'reports' => '*',
            ]),
            User::ROLE_MANAGER => [
                'members.view',
                'reports.view',
            ],
            User::ROLE_MEMBER_LEAD => [
                'reports.view',
            ],
            User::ROLE_MEMBER => [
                'reports.view',
            ],
        ];
    }

    /**
     * Permissions for the GLOBAL (team 0) super-admin role. Note that
     * super admins are additionally granted everything via Gate::before; this
     * matrix exists so the role is meaningful in the DB and in the UI.
     *
     * @return array<int, string>
     */
    public static function superAdminPermissions(): array
    {
        return self::expand([
            'organizations' => '*', 'leads' => '*', 'reports' => '*',
        ]);
    }

    /**
     * Expand a [resource => '*'] map into the full permission list.
     *
     * @param  array<string, string>  $map
     * @return array<int, string>
     */
    private static function expand(array $map): array
    {
        $out = [];
        foreach ($map as $resource => $_) {
            foreach (self::RESOURCES[$resource] ?? [] as $ability) {
                $out[] = "{$resource}.{$ability}";
            }
            $out[] = "{$resource}.manage";
        }

        return array_values(array_unique($out));
    }
}
