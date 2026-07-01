# Tailor — Developer Documentation

A multi-tenant SaaS application built on **Laravel 13**. This is the index for
the per-domain docs. Each file is self-contained — start here, then jump to the
domain you're working in.

---

## Documentation index

| Doc | What it covers |
| --- | --- |
| [01 — Tenancy](01-tenancy.md) | The organization isolation model (single-DB, global scope). |
| [02 — Routing & Middleware](02-routing-and-middleware.md) | The four surfaces + per-surface middleware stacks. |
| [03 — RBAC](03-rbac.md) | Roles & permissions (spatie teams keyed on org id). |
| [04 — Auth & Onboarding](04-auth-and-onboarding.md) | Registration, login, approval lifecycle, impersonation. |
| [05 — Settings](05-settings.md) | Per-org JSON settings + currency (`@money`). |
| [06 — CRUD Module Convention](06-crud-module-convention.md) | **The core dev guide** — the Members/Customers vertical slice + "add a module" recipe. |
| [07 — Dashboards & Reports](07-dashboards-and-reports.md) | Role dashboards + the pluggable report engine. |
| [08 — Public Landing & Leads](08-public-landing-and-leads.md) | Marketing page + central lead capture/triage. |
| [09 — Customer API](09-customer-api.md) | Stateless Sanctum API at `/api/v1/*`. |
| [10 — Frontend, Assets & PWA](10-frontend-assets-and-pwa.md) | Static-asset delivery (no build step) + service worker. |
| [11 — Localization & RTL](11-localization-and-rtl.md) | English/Arabic UI, per-org locale, RTL, enum labels, `x-form.*` components. |

---

## Architecture overview

```
                         ┌───────────────────────────────────────────┐
   Public  ── / ─────────│ marketing landing + central lead capture  │
                         └───────────────────────────────────────────┘
   Super-admin ── /admin/* ── organizations, leads, platform reports  (no org context)
   Tenant admin ── /tenant/* ─ customers, members, roles, settings, reports
   Member      ── /member/* ── member dashboard + reports
   Customer    ── /api/v1/* ── stateless Sanctum JSON API
```

- **Stack:** Laravel `^13.8`, PHP `^8.3`, `stancl/tenancy ^3.10`,
  `spatie/laravel-permission ^7.3`, `laravel/sanctum ^4`,
  `maatwebsite/excel ^3.1`. UI: Bootstrap 5 / Vuexy (static assets).
- **Routes** are split one file per surface, each declaring its own middleware
  group (see [02](02-routing-and-middleware.md)). `bootstrap/app.php` wires them.
- **Controllers are thin**; persistence + listing shape live in **repositories**
  bound interface→concrete in `AppServiceProvider` (see [06](06-crud-module-convention.md)).
- **Actions** (`app/Actions/*`) encapsulate multi-step domain operations
  (register org, change status, update settings).

### The organization isolation model (read this first)

Tenancy runs in **single-database, identification-only mode**: `stancl/tenancy`
only answers *"which organization are we acting as?"*. Isolation is enforced by a
**global Eloquent scope** on `organization_id`, applied by the
`BelongsToOrganization` trait on every tenant-owned model.

```
Request → middleware resolves current Organization (org.init / customer.org.init)
        → OrganizationContext::id() → global OrganizationScope adds
          WHERE organization_id = <current org> to every query
```

- Super-admins have **no org context** → the scope is a no-op → they see all orgs.
- spatie permission "teams" are keyed on the same org id, so role definitions are
  per-organization ([03](03-rbac.md)).
- The same scope isolates the customer API ([09](09-customer-api.md)).

Full detail in [01 — Tenancy](01-tenancy.md). Notable exceptions that are
**deliberately central** (no org scope): `Lead` ([08](08-public-landing-and-leads.md))
and `Organization` itself.

---

## Local setup

Requires PHP 8.3+ and Composer. Default config (`.env.example`) uses **SQLite**
and a **database** queue.

```bash
# 1. Dependencies
composer install

# 2. Environment
cp .env.example .env
php artisan key:generate
# (SQLite) ensure the DB file exists:
touch database/database.sqlite

# 3. Migrate + seed (drops & rebuilds, then seeds permissions + demo tenant)
php artisan migrate:fresh --seed

# 4. Serve
php artisan serve
```

### Seeded login credentials

`migrate:fresh --seed` runs `DatabaseSeeder` → permission/role seeders +
`DemoOrganizationSeeder` (the approved demo org **Acme Inc**, slug `acme`, with
customers). **Password for all accounts: `password`.**

| Email | Role | Lands on |
| --- | --- | --- |
| `super@admin.test` | Super admin (no org) | `/admin/dashboard` |
| `admin@acme.test` | Tenant admin | `/tenant/dashboard` |
| `member@acme.test` | Member | `/member/dashboard` |
| `manager@acme.test` | Manager | `/tenant/dashboard` |
| `lead@acme.test` | Member lead | `/member/dashboard` |

### Queue note

Email is queued (verification + org status-change notifications are
`ShouldQueue`, see [04](04-auth-and-onboarding.md)). The default queue connection
is `database`, so **run a worker** for those emails to send:

```bash
php artisan queue:work
```

For local development you can instead set `QUEUE_CONNECTION=sync` in `.env` to
process jobs inline. By default mail uses the `log` driver (`MAIL_MAILER=log`),
so "sent" mail appears in `storage/logs/laravel.log`.

> Tip: `composer run dev` starts server + queue listener + log tailer
> concurrently (see `composer.json` scripts).

### Other useful commands

```bash
php artisan permissions:sync   # re-sync the permission catalog into the DB (see docs/03)
php artisan test               # run the test suite
```
