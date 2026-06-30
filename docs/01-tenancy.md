# 01 — Tenancy (Organization isolation)

How the app keeps every organization's data separate while running on a
**single shared database**. This is the foundation every other domain builds on.

See also: [Routing & middleware](02-routing-and-middleware.md) ·
[RBAC](03-rbac.md) · [CRUD module convention](06-crud-module-convention.md)

---

## Overview

The app uses [`stancl/tenancy`](https://tenancyforlaravel.com/) in
**identification-only / single-database mode**. Tenancy is *not* used to switch
database connections, cache prefixes, filesystem roots, or queue context. It is
used purely to answer one question: *"which organization are we acting as right
now?"*

Once that question is answered, isolation is enforced by a **global Eloquent
scope** on the `organization_id` column of every tenant-owned table.

```
Request → middleware resolves current Organization → tenancy()->initialize()
        → OrganizationContext::id() reads tenant() → global OrganizationScope
        → every query gets `WHERE organization_id = <current org>`
```

---

## Key files

| File | Responsibility |
| --- | --- |
| `config/tenancy.php` | Single-DB config: empty `bootstrappers`, `id_generator => null`. |
| `app/Models/Organization.php` | The tenant model; implements `Stancl\Tenancy\Contracts\Tenant`. |
| `app/Enums/OrganizationStatus.php` | `pending / approved / suspended / rejected` + `allowsLogin()`. |
| `app/Support/Tenancy/OrganizationContext.php` | Single source of truth for the current org id. |
| `app/Models/Concerns/BelongsToOrganization.php` | Trait added to every org-scoped model. |
| `app/Models/Concerns/OrganizationScope.php` | The global scope that injects the `WHERE` clause. |
| `app/Models/Concerns/FiltersByDateRange.php` | Reusable `dateRange()` query scope. |
| `app/Http/Middleware/InitializeTenancyFromAuthenticatedUser.php` | `org.init` — boots tenancy from the logged-in user. |
| `app/Http/Middleware/InitializeTenancyFromCustomer.php` | `customer.org.init` — boots tenancy for the API guard (see [09](09-customer-api.md)). |

---

## How it works

### 1. The tenant model is `Organization`

`Organization` implements the tenancy `Tenant` contract so it can be handed to
`tenancy()->initialize()`. Because we run single-DB, the key is a plain
auto-increment int (`id_generator => null`):

```php
// app/Models/Organization.php
class Organization extends Model implements Tenant
{
    use HasInternalKeys; // getInternal()/setInternal()
    use SoftDeletes;
    use TenantRun;       // run(callable)

    public function getTenantKeyName(): string { return 'id'; }
    public function getTenantKey(): int|string { return $this->getKey(); }
}
```

`config/tenancy.php` is deliberately stripped down:

```php
'tenant_model' => Organization::class,
'id_generator' => null,        // normal auto-increment ints
'bootstrappers' => [],         // NO db/cache/filesystem/queue switching
```

### 2. `OrganizationContext` resolves the current org id

This is the **one place** that decides the active organization. Resolution
order:

```php
// app/Support/Tenancy/OrganizationContext.php
public static function id(): ?int
{
    if (self::$override !== null) {        // 1. explicit override (for() / seeders)
        return self::$override;
    }
    $tenant = tenant();                    // 2. stancl current tenant
    if ($tenant instanceof Organization) {
        return (int) $tenant->getKey();
    }
    $user = auth()->user();                // 3. fallback: authed user's org
    if ($user !== null && $user->organization_id !== null) {
        return (int) $user->organization_id;
    }
    return null;                           // central / super-admin → no scope
}
```

A `null` result means **central context** (super-admin or a process with no
tenant), in which case the scope is a no-op and queries span all organizations.

`OrganizationContext::for($id, $callback)` temporarily forces a specific org and
restores the previous override afterward — useful in seeders, jobs, and
cross-org admin work.

### 3. `org.init` middleware boots tenancy from the user

For web panels, the current org comes from the authenticated user:

```php
// app/Http/Middleware/InitializeTenancyFromAuthenticatedUser.php  (alias: org.init)
if ($user !== null && $user->organization_id !== null && $user->organization !== null) {
    tenancy()->initialize($user->organization);
}
```

Super-admins (`organization_id === null`) intentionally leave tenancy
uninitialized → they see all organizations.

### 4. The global scope enforces isolation

Every org-owned model uses the `BelongsToOrganization` trait, which registers
`OrganizationScope`:

```php
// app/Models/Concerns/OrganizationScope.php
public function apply(Builder $builder, Model $model): void
{
    $orgId = OrganizationContext::id();
    if ($orgId === null) {
        return; // central context: no constraint
    }
    $builder->where($model->getTable().'.'.$model->getOrganizationKeyName(), $orgId);
}
```

The trait does four more things beyond the read scope:

```php
// app/Models/Concerns/BelongsToOrganization.php
static::creating(...)  // auto-fills organization_id from OrganizationContext::id()
static::updating(...)  // organization_id is immutable — original value restored
// resolveRouteBindingQuery() — route-model binding is org-scoped, so
//   /tenant/projects/{project} can never resolve another org's record
```

Escape hatches (use sparingly, only in central/admin context):

```php
Project::withoutOrganizationScope();   // bypass org scope (soft-deletes intact)
Project::forOrganization($orgId);      // query a specific org regardless of context
```

### 5. Date-range filtering helper

`FiltersByDateRange` adds a reusable scope used by listings and the report engine:

```php
Model::query()->dateRange('created_at', $from, $to);
```

---

## How to extend — make a new model org-scoped

1. Add an `organization_id` foreign key in the migration (indexed,
   `constrained()->cascadeOnDelete()` — see `create_projects_table`).
2. Add the trait to the model:

   ```php
   use App\Models\Concerns\BelongsToOrganization;

   class Invoice extends Model
   {
       use BelongsToOrganization;
   }
   ```

3. That's it. Creation auto-fills `organization_id`, reads are scoped, updates
   can't move the record between orgs, and route-model binding is org-safe.

---

## Gotchas

- **Isolation is application-level, not database-level.** A raw query
  (`DB::table(...)`) or a model **without** the `BelongsToOrganization` trait
  bypasses isolation entirely. Always add the trait.
- **Super-admin context has no scope.** When `OrganizationContext::id()` returns
  `null`, queries span every organization. This is intended for `/admin/*` but
  means any code running without a tenant (and without an authed tenant user)
  sees everything — be deliberate in jobs/commands; wrap with
  `OrganizationContext::for($id, ...)`.
- **`organization_id` is immutable by design.** Trying to change it on update is
  silently reverted, not rejected with an error.
- **`bootstrappers` is empty on purpose.** Do not add the database/cache
  bootstrappers expecting per-tenant databases — this app is single-DB.
- Route-model binding is org-scoped via `resolveRouteBindingQuery()`, so a 404
  (not 403) is what you get when guessing another org's id in the URL.
