# 02 — Routing & Middleware

How requests are split across four surfaces (public, admin, tenant, member) plus
a stateless API, and which middleware guards each.

See also: [Tenancy](01-tenancy.md) · [RBAC](03-rbac.md) ·
[Auth & onboarding](04-auth-and-onboarding.md) · [Customer API](09-customer-api.md)

---

## Overview

Routes are split into **one file per surface**, each declaring its own
middleware group at the top:

| File | Prefix | Name prefix | Audience |
| --- | --- | --- | --- |
| `routes/web.php` | `/` | `home`, `leads.*` | Public marketing + lead capture |
| `routes/auth.php` | `/` | `login`, `register`, … | Guests + authenticated auth flows |
| `routes/admin.php` | `/admin` | `admin.*` | Super-admins (no org) |
| `routes/tenant.php` | `/tenant` | `tenant.*` | Tenant admin / manager |
| `routes/member.php` | `/member` | `member.*` | Member-tier employees |
| `routes/api.php` | `/api/v1` | `api.v1.*` | Customer API (Sanctum) |

`bootstrap/app.php` wires them up. The default `withRouting()` loads
`web`/`api`/`console`, and a `then:` closure loads the extra files:

```php
// bootstrap/app.php
then: function (): void {
    Route::group([], base_path('routes/auth.php'));
    Route::group([], base_path('routes/admin.php'));
    Route::group([], base_path('routes/tenant.php'));
    Route::group([], base_path('routes/member.php'));
},
```

---

## Middleware aliases

All custom aliases are registered in `bootstrap/app.php`:

```php
$middleware->alias([
    'active.user'       => EnsureUserIsActive::class,         // logout if is_active=false
    'central.user'      => EnsureCentralUser::class,          // organization_id === null only
    'member.panel'      => EnsureMemberPanelAccess::class,    // member-tier roles only
    'impersonating'     => HandleImpersonation::class,        // share banner vars to views
    'org.init'          => InitializeTenancyFromAuthenticatedUser::class, // boot tenancy
    'org.approved'      => EnsureOrganizationApproved::class, // org must be Approved
    'super_admin'       => EnsureSuperAdmin::class,           // isSuperAdmin() gate
    'customer.org.init' => InitializeTenancyFromCustomer::class, // API tenancy

    // spatie/laravel-permission
    'role'              => RoleMiddleware::class,
    'permission'        => PermissionMiddleware::class,
    'role_or_permission'=> RoleOrPermissionMiddleware::class,
]);
```

JSON error rendering is enabled for the API **and for any AJAX request that
expects JSON** — the latter is what makes tenant axios forms receive a real
`422` (inline `.invalid-feedback` / notyf) instead of a redirect. Plain browser
form posts still fall through to the standard redirect-with-errors flow:

```php
$exceptions->shouldRenderJsonWhen(
    fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
);
```

---

## The middleware stacks per surface

Each surface stacks its guards in a deliberate order. The key insight:
**`org.init` must run before any org-scoped query**, and the panel-access gates
run after authentication.

```php
// routes/admin.php
['web', 'auth', 'active.user', 'central.user', 'super_admin', 'impersonating']

// routes/tenant.php
['web', 'auth', 'active.user', 'org.init', 'org.approved', 'impersonating']

// routes/member.php
['web', 'auth', 'active.user', 'org.init', 'org.approved', 'member.panel', 'impersonating']
```

What each custom guard does (all are `final readonly` single-method classes):

| Alias | Behaviour |
| --- | --- |
| `active.user` | If `is_active === false`, log out + redirect to login with an error. |
| `central.user` | `abort(403)` unless `organization_id === null`. |
| `super_admin` | `abort(403)` unless `$user->isSuperAdmin()`. |
| `org.init` | If the user has an org, `tenancy()->initialize($user->organization)`. |
| `org.approved` | Log out tenant users whose org is not `Approved`. |
| `member.panel` | `abort(403)` unless `$user->isMemberTier()`. |
| `impersonating` | Share `$isImpersonating` / `$impersonator` to all views (see [04](04-auth-and-onboarding.md)). |

Note `central.user` and `super_admin` are partly redundant on `/admin/*` —
intentional belt-and-braces (one checks "no org", one checks the role).

---

## Per-route permission middleware

On top of the group stack, individual routes add spatie's `permission:` guard.
The convention: **`index` and `listing` share the same view permission**, and
`save` uses the **OR form** `create|update` so one endpoint handles both:

```php
// routes/tenant.php — Projects (the canonical pattern)
Route::get('/',         'index')  ->middleware('permission:projects.view');
Route::get('/listing',  'listing')->middleware('permission:projects.view');   // DataTable JSON
Route::get('/{project}','show')   ->middleware('permission:projects.view');
Route::post('/save',    'save')   ->middleware('permission:projects.create|projects.update');
Route::delete('/{project}','destroy')->middleware('permission:projects.delete');
```

Settings applies the permission at the **group** level instead of per-route:

```php
Route::controller(SettingController::class)->prefix('settings')
    ->middleware('permission:settings.manage')->group(...);
```

---

## The `index` / `listing` / `save` controller convention

Almost every resource controller exposes the same actions, mirrored in the routes:

- `index` — renders the page chrome (the listing view + the save modal).
- `listing` — returns **DataTable JSON** consumed by the client-side table.
- `show` — single record (JSON, used to populate the edit modal).
- `save` — one endpoint for **both create and update** (presence of an id decides).
- `destroy` — delete.
- `dropdowns/*` — small JSON endpoints feeding `<select>` options.

This is documented fully in [CRUD module convention](06-crud-module-convention.md).

---

## How to extend — add a new tenant resource surface

1. Add routes under the appropriate file (usually `routes/tenant.php`) inside a
   `Route::controller(...)->prefix(...)->name(...)->group()` block.
2. Add `permission:<resource>.<ability>` middleware to each route, using the
   `view` permission for both `index` and `listing`, and `create|update` for `save`.
3. Register the resource's permissions in
   [`PermissionCatalog`](03-rbac.md) so the abilities exist.
4. If the resource needs its own panel-level gate, write a `final readonly`
   middleware and register an alias in `bootstrap/app.php`.

---

## Gotchas

- **Order matters:** `org.init` has to come before any controller that runs
  org-scoped queries, otherwise the global scope sees no tenant and (for a
  central user) would span all orgs.
- **`org.approved` / `active.user` actively log the user out** (not just 403) —
  this is how a suspended org or deactivated account is ejected mid-session.
- The `listing` route deliberately shares the `view` permission with `index`;
  don't invent a separate `*.list` permission.
- `save` uses the **pipe OR syntax** (`permission:a|b`). Make sure both
  permissions exist in the catalog or the middleware throws.
