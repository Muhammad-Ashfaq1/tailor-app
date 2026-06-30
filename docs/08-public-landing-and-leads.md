# 08 — Public Landing & Leads

The marketing landing page and the **central** (non-org-scoped) lead capture +
super-admin triage.

See also: [Routing & middleware](02-routing-and-middleware.md) ·
[Auth & onboarding](04-auth-and-onboarding.md) · [Frontend](10-frontend-assets-and-pwa.md)

---

## Overview

`/` is a public marketing page with a "Request a demo" modal. Submissions create
a `Lead` — a **central** record with **no `organization_id`** and no
`BelongsToOrganization` trait, since a prospect doesn't belong to a tenant yet.
Super admins triage leads at `/admin/leads`.

This is the deliberate counter-example to the CRUD convention: a resource that is
intentionally *not* org-scoped.

---

## Key files

| File | Responsibility |
| --- | --- |
| `resources/views/public/home.blade.php` | Landing page + demo modal (axios → `leads.store`). |
| `resources/views/layouts/public.blade.php` | Self-contained landing layout (own CSS, no Vuexy). |
| `app/Http/Controllers/Public/HomeController.php` | Renders landing; bounces authed users to their dashboard. |
| `app/Http/Controllers/Public/LeadController.php` | Public lead capture (JSON or redirect). |
| `app/Http/Requests/StoreLeadRequest.php` | Validation; `status` prohibited from client. |
| `app/Models/Lead.php` | Central model (no org scope). |
| `app/Enums/LeadStatus.php` | `new/contacted/qualified/converted/rejected`. |
| `app/Repositories/LeadRepository.php` | DataTable + create + status update. |
| `app/Http/Controllers/Admin/LeadController.php` | Super-admin triage. |
| `database/migrations/2026_06_30_120002_create_leads_table.php` | `leads` table (no `organization_id`). |
| `routes/web.php`, `routes/admin.php` | Public + admin routes. |

---

## How it works

### 1. The landing page bounces authed users

```php
// app/Http/Controllers/Public/HomeController.php
public function index(): View|RedirectResponse
{
    if (auth()->check()) {
        return redirect()->route(auth()->user()->defaultDashboardRouteName());
    }
    return view('public.home');
}
```

The landing layout (`layouts/public.blade.php`) is **self-contained**: it ships
its own namespaced `*-landing` CSS (so it never collides with the Vuexy admin
theme) and only loads axios + `app.js`. It still includes the PWA head partial.

### 2. Lead capture (central, throttled)

The demo modal posts JSON via axios to `leads.store` (throttled `5,1`):

```php
// routes/web.php
Route::post('/leads', [LeadController::class, 'store'])
    ->middleware('throttle:5,1')->name('leads.store');
```

```php
// app/Http/Controllers/Public/LeadController.php
public function store(StoreLeadRequest $request): JsonResponse|RedirectResponse
{
    $this->leads->create($request->payload());
    $message = 'Thanks — we will be in touch.';
    return ($request->expectsJson() || $request->wantsJson())
        ? response()->json(['message' => $message])
        : back()->with('status', $message);   // graceful no-JS fallback
}
```

`status` is **server-set** — the request prohibits it and the repository forces
`LeadStatus::New` on create:

```php
// StoreLeadRequest::rules()
'status' => ['prohibited'],
// LeadRepository::create()
$data['status'] = LeadStatus::New->value;
```

### 3. The Lead model is intentionally NOT org-scoped

```php
// app/Models/Lead.php — no BelongsToOrganization, no organization_id
class Lead extends Model
{
    protected $fillable = ['name','email','company','message','status'];
    protected function casts(): array { return ['status' => LeadStatus::class]; }
}
```

The migration confirms it: the `leads` table has **no `organization_id`** column.

### 4. Super-admin triage

`/admin/leads` (guarded by the super-admin stack, see
[routing](02-routing-and-middleware.md)) follows the same `index` + `listing`
DataTable pattern as other resources, plus an inline status update:

```php
// app/Http/Controllers/Admin/LeadController.php
public function updateStatus(Request $request, Lead $lead): JsonResponse
{
    $validated = $request->validate(['status' => ['required', Rule::enum(LeadStatus::class)]]);
    $lead = $this->leads->updateStatus($lead, $validated['status']);
    return response()->json([...]);
}
```

`LeadRepository::datatable()` runs `Lead::query()` with **no org scope** (correct
— super admins see all leads) and reuses the shared `DataTableBuilder`.

---

## How to extend

- **Add a landing section/feature:** edit the `$features` array and markup in
  `resources/views/public/home.blade.php`.
- **Capture extra lead fields:** add the column (migration), `$fillable`
  (`Lead`), rules + `payload()` (`StoreLeadRequest`), the modal input, and the
  triage table column.
- **Convert a lead → organization:** wire a triage action to
  `RegisterOrganizationAction` (see [onboarding](04-auth-and-onboarding.md)) and
  set the lead status to `converted`.

---

## Gotchas

- Leads are **central by design** — do **not** add `BelongsToOrganization` or an
  `organization_id`. They predate any tenant.
- The public lead route is **throttled** (`5,1`); keep that to deter spam.
- `status` must never be client-supplied — it's prohibited in the request and
  forced in the repository.
- The landing CSS is namespaced (`*-landing`) on purpose; reuse that prefix so
  styles don't leak into the admin theme.
- The lead `listing` is under `/admin/*`, where `org.init` does not run, so no
  org scope applies — which is exactly what triage needs.
