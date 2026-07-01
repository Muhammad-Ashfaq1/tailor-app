# 04 — Auth & Onboarding

Self-service organization registration, the layered login flow, super-admin
approval lifecycle, and impersonation.

See also: [Tenancy](01-tenancy.md) · [RBAC](03-rbac.md) ·
[Routing & middleware](02-routing-and-middleware.md) · [Settings](05-settings.md)

---

## Overview

The auth surface is hand-rolled (no Breeze/Jetstream). Flow:

```
Register → PENDING org + INACTIVE admin user → verify email
        → super-admin approves → org Approved + users activated → can log in
```

Login is **layered**: credentials → email verified → org approved → account
active, each with its own message. Two account states actively eject a
mid-session user (deactivated account, non-approved org).

---

## Key files

| File | Responsibility |
| --- | --- |
| `app/Actions/Auth/RegisterOrganizationAction.php` | Creates pending org + inactive admin, provisions roles. |
| `app/Http/Controllers/Auth/RegisterController.php` | Register form + store. |
| `app/Http/Controllers/Auth/AuthController.php` | Login form + layered login + logout (one controller). |
| `app/Http/Controllers/Auth/EmailVerificationController.php` | Notice / verify / resend. |
| `app/Http/Controllers/Auth/PasswordResetController.php` | Forgot + reset. |
| `app/Http/Controllers/Auth/ImpersonationController.php` | Shared stop-impersonating handler. |
| `app/Http/Requests/Auth/{Register,Login}Request.php` | Validation. |
| `app/Actions/Organizations/ChangeOrganizationStatusAction.php` | Approve/suspend/reject + sync users. |
| `app/Notifications/OrganizationStatusChangedNotification.php` | Queued status mail. |
| `app/Support/Impersonation/Impersonator.php` | Start/stop impersonation engine. |
| `routes/auth.php` | All auth routes (guest + authenticated). |

---

## How it works

### 1. Registration (one transaction)

`RegisterOrganizationAction` creates a **pending** org and an **inactive** admin
user, provisions the org's roles, assigns `tenant_admin`, then queues the
verification email *outside* the transaction:

```php
// app/Actions/Auth/RegisterOrganizationAction.php
$organization = Organization::create([
    'name'   => $data['organization_name'],
    'slug'   => $this->uniqueSlug($data['organization_name']),
    'status' => OrganizationStatus::Pending,
]);

$user = new User([...'role' => User::ROLE_TENANT_ADMIN]);
$user->is_active = false;       // activated on approval
$user->save();

$this->provisionRoles->handle($organization->id);
$user->assignPrimaryRole(User::ROLE_TENANT_ADMIN);
// ...then: $user->sendEmailVerificationNotification();
```

The register request **prohibits** tenancy columns from the client:

```php
// RegisterRequest::rules()
'organization_id' => ['prohibited'],
'role'            => ['prohibited'],
'is_active'       => ['prohibited'],
```

After registering, the user is logged in and sent to the "verify your email"
notice.

### 2. Layered login

`AuthController::login()` checks credentials manually, then walks the block
reasons in order:

```php
// app/Http/Controllers/Auth/AuthController.php  (showLogin / login / logout)
// 1. Credentials (Hash::check)
// 2. Email verified?
// 3. Organization approved? (pending/suspended/rejected → distinct message)
// 4. is_active?
Auth::login($user, $request->boolean('remember'));
return redirect()->intended(route($user->defaultDashboardRouteName()))
    ->with('status', "Welcome back, {$user->name}!"); // success toast on the dashboard
```

`defaultDashboardRouteName()` sends them to `admin.dashboard`,
`member.dashboard`, or `tenant.dashboard` by tier (see [RBAC](03-rbac.md)).

### 3. Mid-session ejection

Two middleware actively log a user out rather than 403 (see
[routing](02-routing-and-middleware.md)):

- `active.user` — if `is_active` flipped to false.
- `org.approved` — if the org is no longer `Approved`.

### 4. Approval lifecycle

Super admins change org status via `ChangeOrganizationStatusAction`, which keeps
user `is_active` flags consistent and notifies the org's admins:

```php
// app/Actions/Organizations/ChangeOrganizationStatusAction.php
$organization->status = $status;  $organization->save();
$isApproved = $status === OrganizationStatus::Approved;
User::query()->where('organization_id', $organization->id)
    ->update(['is_active' => $isApproved]);   // approve activates, else deactivates
// then Notification::send($admins, new OrganizationStatusChangedNotification(...));
```

`OrganizationStatusChangedNotification` is a **queued** mail (`ShouldQueue`) — so
the queue must be running for emails to send (see [README](README.md)).

### 5. Impersonation

`Impersonator` stashes the original user id in the session and logs in the
target. One shared `stop()` restores the original — used by both
super-admin→tenant and tenant-admin→member flows:

```php
// app/Support/Impersonation/Impersonator.php
public const SESSION_KEY = 'impersonator_id';

public function start(User $target): void
{
    if (! session()->has(self::SESSION_KEY)) {
        session()->put(self::SESSION_KEY, Auth::id());
    }
    Auth::login($target);
}
```

- **Start** lives in the admin/tenant controllers (`organizations.impersonate`,
  `members.impersonate`).
- **Stop** is the single `POST /impersonate/stop` route guarded by the
  `impersonating` middleware.
- `HandleImpersonation` middleware shares `$isImpersonating` and `$impersonator`
  to every view so a banner can render. It looks up the impersonator with
  `User::withoutGlobalScopes()` (the original may be a super admin / different org).

---

## How to extend

- **Add a login gate** (e.g. "must accept ToS"): add a branch to
  `AuthController::resolveLoginBlockMessage()`.
- **Add a new org status transition side-effect**: extend
  `ChangeOrganizationStatusAction::handle()`.
- **Change what a new org's first user gets**: edit `RegisterOrganizationAction`
  (role, active flag, default settings).

---

## Gotchas

- **The queue must be running** for verification + status emails (both queued).
  Use `php artisan queue:work`, or set `QUEUE_CONNECTION=sync` locally.
- New admin users are **inactive until approval** — a freshly registered org
  cannot log in until a super admin approves it.
- Impersonation lookups use `withoutGlobalScopes()` because the real user often
  belongs to a different org (or none).
- `org.init` does not run on `/admin/*`, so a super admin impersonating a tenant
  admin gets tenancy from the **target** user once they hit `/tenant/*`.
