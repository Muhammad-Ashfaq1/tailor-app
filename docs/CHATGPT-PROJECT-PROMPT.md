# ChatGPT Implementation Prompt — Tailor Shop Management (Web Admin)

> Copy everything below the line into ChatGPT as your first message. It gives full
> project context, the exact conventions, and the domain scope. Then implement one
> module at a time by pasting the "Next module" block at the bottom.

---

## ROLE

You are a senior Laravel engineer working on an **existing** multi-tenant SaaS
codebase. You must follow the codebase's established conventions **exactly** — do
not invent new patterns, do not add a build step, do not restructure anything.
When you generate code, output complete files with correct paths, following the
recipes below.

## THE PRODUCT

A **tailoring / stitching shop management system** for a business in **Saudi
Arabia**. Multiple shops can use the platform (multi-tenant). **Right now we are
building ONLY two surfaces:**

1. **Shop Admin panel** (`/tenant/*`) — a shop owner/staff manages the whole shop.
2. **Super Admin panel** (`/admin/*`) — platform owner manages shops (organizations).

> A Flutter customer app + customer API (`/api/v1/*`) is planned for LATER. Do NOT
> build customer-facing features now, but **design the data model so the customer
> app can consume it later** (e.g. customers, measurements, orders, wallet already
> exist as org-scoped tables).

## TECH STACK (fixed — do not change)

- Laravel `^13.8`, PHP `^8.3`
- `stancl/tenancy ^3.10` in **single-database, identification-only mode**
- `spatie/laravel-permission ^7.3` (teams keyed on `organization_id`)
- `laravel/sanctum ^4` (for the future customer API only)
- `maatwebsite/excel ^3.1` (report exports)
- UI: **Bootstrap 5 / Vuexy** as **static assets** under `public/organization/`.
  **NO Vite/webpack in the request path.** jQuery + DataTables + Select2 +
  SweetAlert2 + axios, loaded as plain `<script>` tags.

## ARCHITECTURE YOU MUST RESPECT

### Multi-tenancy (org isolation) — the #1 rule
- Each **Organization = one shop** (the tenant). It is the `stancl/tenancy` tenant
  model but runs single-DB.
- Isolation is a **global Eloquent scope on `organization_id`**, applied by the
  `App\Models\Concerns\BelongsToOrganization` trait. **Every shop-owned model MUST
  use this trait.**
- The trait: auto-fills `organization_id` on create, makes it immutable on update,
  scopes all reads, and makes route-model binding org-safe (foreign id → 404).
- `App\Support\Tenancy\OrganizationContext::id()` is the single source of truth for
  the current shop id. `null` = central/super-admin context (sees all shops).
- **NEVER** put `organization_id` in `$fillable`; **NEVER** accept it from the
  client — the FormRequest must `prohibited` it. Raw `DB::table()` queries bypass
  isolation — always go through Eloquent models with the trait.

### Surfaces & routing (one file per surface)
| File | Prefix | Name prefix | Middleware stack |
| --- | --- | --- | --- |
| `routes/tenant.php` | `/tenant` | `tenant.*` | `web, auth, active.user, org.init, org.approved, impersonating` |
| `routes/admin.php` | `/admin` | `admin.*` | `web, auth, active.user, central.user, super_admin, impersonating` |
| `routes/member.php` | `/member` | `member.*` | `+ member.panel` |

`org.init` must run before any org-scoped query (it boots tenancy from the logged-in
user). Super-admin routes have NO `org.init` → they span all shops.

### RBAC
- Permissions are **global**, defined once in
  `app/Support/Permissions/PermissionCatalog.php` under `RESOURCES`
  (`<resource>.manage` is auto-added). Roles are **per-org**, provisioned from
  `tenantMatrix()`.
- To add a resource: add `'fabrics' => ['view','create','update','delete']` to
  `RESOURCES`, grant it in `tenantMatrix()` roles, run `php artisan permissions:sync`,
  then **re-provision org roles** (the matrix only applies at provisioning — run the
  demo seed or call `ProvisionOrganizationRoles` per org).
- Guard routes: `permission:fabrics.view` on `index`+`listing`+`show`,
  `permission:fabrics.create|fabrics.update` on `save`, `permission:fabrics.delete`
  on `destroy`.

### The CRUD Module Convention (follow for EVERY module)
Each shop resource is one vertical slice:
```
Migration → Model (+ Enum) → Repository (Interface + impl) → FormRequest
          → Controller → Routes (permissions) → Blade (listing + one save modal)
          + Factory + Permissions in the catalog
```
Controller actions (mirrored in routes):
- `index` — renders page chrome (listing view + save modal).
- `listing` — returns **DataTable JSON** via `App\Support\DataTables\DataTableBuilder`.
- `show` — single record JSON (populates edit modal).
- `save` — **ONE endpoint for create AND update** (presence of `id` decides).
- `destroy` — delete.
- `dropdowns/*` — small JSON endpoints feeding `<select>` options (Select2).

Rules baked into the convention:
- Repositories own persistence + listing shape; bind interface→concrete in
  `AppServiceProvider::$repositories`. Extend `BaseRepository` (stamps
  `created_by`/`updated_by` via `withAudit()`). Add `HandlesSlugs` if the resource
  is slug-routed (composite unique `['organization_id','slug']`).
- `DataTableBuilder::for($query,$request)->searchable([...])->orderable([...])->map(fn)->toArray()`.
  `orderable()` is a **whitelist**; only real DB columns.
- FormRequest: `authorize()` splits create/update perms; `prohibited` on
  `organization_id`/`created_by`/`updated_by`; **org-scope every foreign key** with
  `Rule::exists('table','id')->where('organization_id', $orgId)` so you can't attach
  another shop's row.
- Migration: `foreignId('organization_id')->constrained()->cascadeOnDelete()`, your
  columns, `created_by`/`updated_by` nullable FKs, `timestamps()`, `softDeletes()`.
- Blade: copy `resources/views/tenant/members/index.blade.php` as the template — a
  server-side DataTable + ONE save modal (hidden `id` decides create/edit),
  `axios.post` submit, 422 → inline `.invalid-feedback`, success → reload + SweetAlert.
  Per-page libs via `@push('vendor-scripts')`. Gate buttons with `@can(...)`.
- Enum: `app/Enums/XStatus.php` with `label()`, `color()`, `options()`.

### Settings & currency
- Per-org settings live in a JSON column, schema-driven via
  `app/Support/Settings/SettingsSchema.php` + `OrganizationSettings::DEFAULT_SETTINGS`
  (deep-merged on read — new keys need no migration).
- Currency: `@money($amount)` Blade directive + `window.formatMoney()` JS, both driven
  by the org's `regional.currency` setting. **Use `@money` for every price/amount** —
  default the shop currency to **SAR**.

### Reports & dashboards
- A report = one class extending `App\Support\Reports\ReportDefinition` + one line in
  `ReportRegistry::$map`. It gets a filtered DataTable + Excel export automatically on
  every panel. `baseQuery()` must start from an org-scoped model.
- Tenant dashboard payload is built in `App\Services\TenantDashboardService::build()`
  (stats, by-status breakdowns, 14-day trend via `dateRange()`), rendered with
  ApexCharts via `@json`.

### What already exists (do NOT rebuild)
Tenancy, RBAC, auth/onboarding/impersonation, settings, currency, reports engine,
dashboards scaffolding, DataTableBuilder, BaseRepository, the Members & Roles &
Settings modules, Leads (central), Customer model + Sanctum API skeleton. Projects &
Tasks were the example modules and have been **removed** — you are replacing them with
the tailoring domain.

---

## DOMAIN MODEL TO BUILD (shop-admin `/tenant/*`, all org-scoped)

Build these as CRUD modules following the convention above. Suggested order (each
depends on the ones before it):

### 1. `Customer` management (extend existing `Customer` model)
- A `customers` table already exists (org-scoped, Sanctum identity). Extend it with
  tailoring fields; build the **tenant CRUD UI** for it (currently only the API stub exists).
- Fields: name, phone, email (nullable), address, `type` enum (`walk_in`, `regular`),
  notes. Credit/loyalty config: `credit_type` enum (`percentage`, `fixed`, `none`),
  `credit_value` (decimal) — the reward a customer earns each visit/order.
- Relations: hasMany measurements, orders, wallet transactions.

### 2. `Measurement` (naap)
- Belongs to a customer. Stores body measurements as structured fields (e.g. length,
  chest, shoulder, sleeve, neck, waist, etc.) — model as individual decimal columns
  OR a JSON `values` column keyed by a measurement template (recommend JSON + a small
  fixed template for flexibility).
- A customer can have measurement history (versioned); latest is the active one.
- Status/approval: a future customer-app can request changes; for now include an
  `is_approved`/`status` flag so the shop admin can approve edits.

### 3. `Fabric` / Cloth inventory
- Fields: name, `serial_number` (**auto-generated, unique per org** — use a
  per-org sequence, e.g. `FAB-000123`), category, `stock_quantity`, unit
  (meter/yard), cost_price, sale_price, description.
- **Variants**: colors + variants. Model a `fabric_variants` (or `fabric_colors`)
  child table (color name/hex, variant label, its own stock + optional price
  override, its own serial/SKU). Stock tracked at variant level.
- Auto-serial generation must be org-safe (unique within the shop, like the slug
  helper `HandlesSlugs::generateUniqueSlug()` is org-safe).

### 4. `StitchingType` (silai design + pricing)
- The stitching styles: **Saudi, Arabi (Emirati/Amarati), Yamani (Yemeni),
  Kuwaiti**, etc. Seed these as configurable rows per shop (so each shop sets its own).
- Fields: name, `base_price` (the preset price), description, active flag.
- Prices are **adjustable per order** (see Orders) — some customers pay more, some
  less, some the preset. Do NOT hardcode; store the base price here and allow an
  override amount on the order line.

### 5. `Order` (the core — a suit/dress order)
- Belongs to a customer. Fields: `order_number` (auto-generated, unique per org),
  `status` enum, `cloth_source` enum (`customer_own`, `shop`), fabric_id +
  fabric_variant_id (nullable when customer brings own cloth), stitching_type_id,
  `base_price`, `adjusted_price` (the actual charged price — can be > / < / = base),
  quantity, `subtotal`, discount, `credit_applied`, delivery fields, `total`,
  `amount_paid`, `balance`, `estimated_ready_at` (estimated time), notes.
- **Status workflow** (admin can add/update): `measured` → `in_progress` →
  `completed` → `delivered` (+ maybe `cancelled`). Model as an enum with
  `label()`/`color()`. The customer app will read this status later.
- **Delivery**: `delivery_type` enum (`shop_pickup`, `home_delivery`),
  delivery_address, `delivery_charges` (extra charge on home delivery).
- When cloth_source = `shop`, **decrement the chosen fabric variant's stock** on order
  creation (do this in the repository `save()`, inside a DB transaction).
- Line-items: if an order can contain multiple suits/items, add an `order_items`
  child table (item = fabric/variant + stitching type + measurement snapshot +
  price). Recommend order_items for flexibility; a single-item order is just one row.

### 6. `Payment`
- Belongs to an order (and/or a customer wallet top-up). Fields: `amount`,
  `method` enum (`cash`, `online`, `pos`, `wallet`), reference/txn no, paid_at, notes.
- Recording a payment updates the order's `amount_paid`/`balance`.

### 7. `Wallet` (customer credits/balance)
- A `wallet_transactions` table (org-scoped, belongs to customer): `type` enum
  (`credit`, `debit`), `amount`, `source` enum (`order_reward`, `top_up`,
  `order_payment`, `adjustment`), reference (order id / payment id), balance_after,
  notes. Customer's current balance = latest `balance_after` (or a cached column on
  customer).
- Earning: when an order completes, credit the customer per their `credit_type`/
  `credit_value`. Spending: a wallet balance can pay toward an order.

### 8. Employee / Labor management
- `Employee` (may or may not be a `User` — model as a separate org-scoped
  `employees` table for non-login staff): name, phone, role/designation,
  `salary_type` (`monthly`, `daily`, `per_piece`), `base_salary`, join_date, active.
- `Attendance` (hazri / "garigari"): employee_id, date, status
  (`present`/`absent`/`half_day`/`leave`), hours, notes. Unique per
  (employee, date).
- `SalaryPayment`: employee_id, period (month), gross, deductions, `advance_deducted`,
  net_paid, paid_at.
- `Advance`: employee_id, amount, given_at, notes, `is_settled` (advances given, later
  deducted from salary).

### 9. `Expense` (shop expenses)
- Fields: `category` enum (rent, utilities/bills, supplies, salary, misc…), title,
  `amount`, `spent_at`, payment_method, notes, receipt attachment (optional).

### 10. Dashboard (extend `TenantDashboardService`)
- Realtime stats: today's/this-month's revenue, orders by status, pending balance,
  low-stock fabrics, orders due soon (estimated_ready_at), new customers, expense
  total, employee attendance summary. Feed ApexCharts.

### 11. Reports
- Add report classes (one line each in `ReportRegistry`): Orders, Payments, Expenses,
  Fabric stock, Customer ledger, Employee salary/attendance. Each gets DataTable +
  Excel export free.

## SUPER ADMIN side (`/admin/*`) — mostly exists
- Organizations (shops) CRUD + approve/suspend lifecycle: **already exists**.
- Leads triage: already exists.
- Add: platform-level reports (shops count, active orders across shops, revenue per
  shop) if requested — using the central (unscoped) query pattern like the admin
  dashboard. Otherwise leave the super-admin side as-is.

## DELIVERABLES PER MODULE (output all of these, full files, correct paths)
1. Migration(s)
2. Enum(s)
3. Model(s) — with `BelongsToOrganization`, `FiltersByDateRange`, casts, relations,
   `$fillable` (no `organization_id`)
4. Repository Interface + implementation (extend `BaseRepository`, `HandlesSlugs` if
   slugged), and the `AppServiceProvider` binding line
5. FormRequest(s) — create/update authorize split, prohibit server-set columns,
   org-scope every FK
6. Controller — `index`/`listing`/`show`/`save`/`destroy`/`dropdowns`
7. Routes block for `routes/tenant.php` with correct `permission:` middleware
8. `PermissionCatalog` additions + `tenantMatrix()` grants (+ note to run
   `permissions:sync` and re-provision roles)
9. Blade view (listing DataTable + one save modal), following the members template
10. Factory + a note for the `DemoOrganizationSeeder` demo data
11. Report class + `ReportRegistry` line (where applicable)

## CONSTRAINTS / DON'TS
- Do NOT add `organization_id` to `$fillable` or accept it from the client.
- Do NOT use raw `DB::table()` for shop data (bypasses isolation).
- Do NOT add a Vite/build dependency for admin pages — static assets only.
- Do NOT create per-`*.list` permissions — `index` & `listing` share `*.view`.
- Do NOT hardcode currency — use `@money` / `formatMoney` (SAR default).
- Use DB transactions for multi-step writes (order + stock decrement + wallet + payment).
- Slugs/serials/order numbers must be unique **per org**, generated org-safely.
- Keep controllers thin; logic in repositories/actions.

## HOW WE'LL WORK
I'll ask for **one module at a time**. For each, produce the full vertical slice
(all deliverables above), ask me nothing you can infer from these conventions, and
end with the exact artisan/commands I need to run (`migrate`, `permissions:sync`,
re-provision, seed).

---

### Next module: <PASTE THE MODULE NAME HERE, e.g. "Fabric inventory with variants">
