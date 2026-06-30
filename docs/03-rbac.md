# 03 — RBAC (Roles & Permissions)

Authorization built on [`spatie/laravel-permission`](https://spatie.be/docs/laravel-permission)
with **teams keyed on `organization_id`**, so the same role names mean different
permission sets in each organization.

See also: [Tenancy](01-tenancy.md) · [Auth & onboarding](04-auth-and-onboarding.md) ·
[Routing & middleware](02-routing-and-middleware.md)

---

## Overview

- **Permissions are global** (not team-scoped). Defined once in
  `PermissionCatalog`.
- **Roles are per-organization.** spatie's "teams" feature is enabled with the
  team id = the current organization id. Each org gets its own copies of
  `tenant_admin`, `manager`, `member_lead`, `member`.
- **`super_admin`** lives on the **global team (id `0`)** and is additionally
  short-circuited to "allow everything" via `Gate::before`.
- The current team is resolved automatically from the tenancy context by a
  custom resolver, so you rarely set it by hand.

---

## Key files

| File | Responsibility |
| --- | --- |
| `config/permission.php` | `teams => true`, `team_foreign_key => team_id`, custom `team_resolver`. |
| `app/Support/Permissions/PermissionCatalog.php` | Single source of truth: all permissions + role→permission matrix. |
| `app/Support/Permissions/OrganizationPermissionTeamResolver.php` | Maps spatie "current team" → current org id (0 = central). |
| `app/Support/Permissions/PermissionTeamScope.php` | `for($orgId, $cb)` — pin the team for a block. |
| `app/Actions/Organizations/ProvisionOrganizationRoles.php` | Creates the 4 tenant roles + wires the matrix for one org. |
| `database/seeders/PermissionSeeder.php` | Seeds every permission (global). |
| `database/seeders/RolePermissionSeeder.php` | Seeds the global `super_admin` role (team 0). |
| `app/Console/Commands/SyncPermissions.php` | `php artisan permissions:sync` — re-runs the permission seeder. |
| `app/Providers/AuthServiceProvider.php` | Registers policies + super-admin `Gate::before`. |
| `app/Policies/{Project,Task}Policy.php` | Model-level ability checks. |
| `app/Models/User.php` | Role constants, `assignPrimaryRole()`, `defaultDashboardRouteName()`. |

---

## How it works

### 1. The permission catalog is the source of truth

`PermissionCatalog` declares every resource's abilities. An aggregate
`<resource>.manage` is auto-added per resource:

```php
// app/Support/Permissions/PermissionCatalog.php
private const RESOURCES = [
    'projects'      => ['view', 'create', 'update', 'delete'],
    'tasks'         => ['view', 'create', 'update', 'delete'],
    'members'       => ['view', 'create', 'update', 'delete', 'impersonate'],
    'roles'         => ['view'],
    'settings'      => [],
    'reports'       => ['view'],
    'organizations' => ['view', 'create', 'update', 'delete'], // central
    'leads'         => ['view', 'update'],                     // central
];
```

The same class also declares the default role→permission matrix:

```php
public static function tenantMatrix(): array
{
    return [
        User::ROLE_TENANT_ADMIN => self::expand([...all tenant resources...]),
        User::ROLE_MANAGER      => ['projects.view','projects.create',...,'reports.view'],
        User::ROLE_MEMBER_LEAD  => ['projects.view','tasks.view',...,'reports.view'],
        User::ROLE_MEMBER       => ['tasks.view','tasks.update','reports.view'],
    ];
}
```

### 2. Teams are keyed on organization id

`config/permission.php` enables teams and points spatie at the custom resolver:

```php
'teams' => true,
'team_resolver' => \App\Support\Permissions\OrganizationPermissionTeamResolver::class,
'column_names' => ['team_foreign_key' => 'team_id'],
```

The resolver mirrors `OrganizationContext`'s logic (explicit override → tenant →
authed user → `0`):

```php
// OrganizationPermissionTeamResolver::getPermissionsTeamId()
if ($this->explicitlySet)         return $this->teamId;
if (tenant() instanceof Organization) return tenant()->getKey();
if ($user?->organization_id)      return $user->organization_id;
return 0; // central / global team
```

To read or write pivots for a *different* org than the ambient one, wrap with
`PermissionTeamScope::for($orgId, fn () => ...)` — it sets and restores the team.

### 3. Roles are provisioned per organization

When an org is created (registration, demo seeder), `ProvisionOrganizationRoles`
creates that org's roles on its team and syncs the matrix. Idempotent:

```php
// ProvisionOrganizationRoles::handle()
PermissionTeamScope::for($organizationId, function () use ($organizationId) {
    foreach (PermissionCatalog::tenantMatrix() as $roleName => $permissions) {
        $role = Role::firstOrCreate(
            ['name' => $roleName, 'team_id' => $organizationId, 'guard_name' => 'web'],
        );
        $role->syncPermissions($permissions);
    }
});
```

### 4. The primary role + a fast-path mirror

`users.role` is a denormalised string mirror of the user's primary spatie role
(used by middleware/dashboards without hitting the pivot tables).
`assignPrimaryRole()` keeps both in sync, scoped to the org team:

```php
// app/Models/User.php
public function assignPrimaryRole(string $role): void
{
    $teamId = $this->organization_id ?? 0;
    PermissionTeamScope::for($teamId, fn () => $this->syncRoles([$role]));
    $this->forceFill(['role' => $role])->save();
}
```

Role tiers and routing live on the model too:

```php
public function isSuperAdmin(): bool  // role === super_admin OR organization_id === null
public function isMemberTier(): bool  // role in [member, member_lead]
public function defaultDashboardRouteName(): string // admin/member/tenant dashboard
```

### 5. Super-admin short-circuit + policies

`AuthServiceProvider` registers policies and a global allow-all for super admins:

```php
Gate::before(static fn (User $user): ?bool => $user->isSuperAdmin() ? true : null);
```

Policies are thin ability checks (the org scope already guarantees ownership):

```php
// app/Policies/ProjectPolicy.php
public function update(User $user, Project $project): bool
{
    return $user->hasPermissionTo('projects.update');
}
```

### 6. Enforcement points

- **Routes:** `permission:projects.view`, `permission:projects.create|projects.update`
  (see [routing](02-routing-and-middleware.md)).
- **Form requests:** `authorize()` calls `$this->user()->can('settings.manage')`.
- **Blade:** `@can('projects.create')` to show/hide UI.
- **Policies:** `Gate`/`authorize()` for model actions.

---

## How to extend

### Add a permission to an existing resource

1. Add the ability to the resource's array in `PermissionCatalog::RESOURCES`.
2. Add it to the relevant roles in `tenantMatrix()` (or `superAdminPermissions()`).
3. Run `php artisan permissions:sync` to insert the new permission rows.
4. **Re-provision org roles** if you changed the matrix — run the demo seed or
   call `ProvisionOrganizationRoles` for each org (the matrix only applies on
   provisioning, so existing orgs need a re-sync).

### Add a whole new resource

1. Add `'invoices' => ['view','create',...]` to `RESOURCES`.
2. Grant it in the role matrices.
3. `permissions:sync`, then guard routes with `permission:invoices.*`.

### Add a new role

Add a constant to `User`, include it in `TENANT_ROLES` (and `tableMatrix`), and
add its permission set to `tenantMatrix()`.

---

## Gotchas

- **Permissions are global, roles are team-scoped.** Don't try to scope a
  permission per org; scope the role instead.
- **The matrix is applied at provisioning time only.** Editing `tenantMatrix()`
  does not retroactively update existing orgs' roles — re-run provisioning.
- **`super_admin` bypasses every check** via `Gate::before`. Its DB permission
  matrix exists only so the role is meaningful in the UI.
- The global team is **`0`**, not `null` — `RolePermissionSeeder::GLOBAL_TEAM = 0`.
- Always go through `assignPrimaryRole()` (not raw `syncRoles`) so the
  `users.role` mirror stays correct.
