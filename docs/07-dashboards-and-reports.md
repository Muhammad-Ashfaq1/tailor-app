# 07 — Dashboards & Reports

The three role dashboards and the pluggable report engine (DataTable + Excel
export) shared across every panel.

See also: [CRUD module convention](06-crud-module-convention.md) ·
[Routing & middleware](02-routing-and-middleware.md) · [RBAC](03-rbac.md)

---

## Overview

- **Dashboards:** one controller per tier (`Tenant`, `Member`, `Admin`). The
  tenant dashboard delegates to a service that builds a fully org-scoped,
  JSON-safe payload for ApexCharts.
- **Reports:** a small engine where **one report = one class + one registry
  line**. A single surface-agnostic `ReportController` serves the tenant, admin,
  and member panels, deriving its layout from the route name. Reports get a
  filtered DataTable and a matching `.xlsx` export for free.

---

## Key files

| File | Responsibility |
| --- | --- |
| `app/Services/TenantDashboardService.php` | Builds the tenant dashboard payload (stats, by-status, 14-day trend). |
| `app/Http/Controllers/Tenant/DashboardController.php` | Renders `tenant.dashboard`. |
| `app/Http/Controllers/Member/DashboardController.php` | "My tasks" counts by status. |
| `app/Http/Controllers/Admin/DashboardController.php` | Platform overview (orgs by status, user count). |
| `app/Support/Reports/ReportDefinition.php` | Abstract base: query, columns, filters, summary, export glue. |
| `app/Support/Reports/ReportRegistry.php` | `key => DefinitionClass` map. |
| `app/Reports/{Projects,Tasks}Report.php` | Concrete reports. |
| `app/Repositories/ReportsRepository.php` | Runs a report's filtered DataTable. |
| `app/Http/Controllers/ReportController.php` | Surface-agnostic controller (index/show/listing/export). |
| `app/Exports/ReportExport.php` | maatwebsite/excel export reusing the definition. |

---

## Dashboards

### Tenant dashboard service

`TenantDashboardService::build()` returns a plain array. Everything is
org-scoped automatically (Project/Task carry the global scope; active members
filter by the current org id):

```php
// app/Services/TenantDashboardService.php
return [
    'stats'              => $this->stats(),            // total projects/tasks, active members, open tasks
    'tasks_by_status'    => $this->tasksByStatus(),    // [{value,label,color,count}, ...]
    'projects_by_status' => $this->projectsByStatus(),
    'trend'              => $this->taskTrend(),         // last 14 days: {labels, data}
];
```

The trend reuses the `dateRange()` scope from `FiltersByDateRange` (docs/01),
then back-fills zero-count days. The controller just hands the payload to the
view, which feeds ApexCharts via `@json`.

### Member & admin dashboards

- **Member:** counts the *authenticated user's* tasks by status
  (`where('assigned_to', auth()->id())`) — org isolation is automatic.
- **Admin:** `Organization` is the tenant model (no org scope), so a plain query
  spans all tenants — counts organizations by status + total users.

---

## Reports engine

### A report is a `ReportDefinition` subclass

The base class (`app/Support/Reports/ReportDefinition.php`) declares the
contract and shares filter/summary/mapping logic. A concrete report only
describes its data:

```php
// app/Reports/TasksReport.php
final class TasksReport extends ReportDefinition
{
    public function key(): string   { return 'tasks'; }
    public function label(): string { return 'Tasks'; }

    public function baseQuery(): Builder        // org-scoped automatically
    { return Task::query()->with(['project', 'assignee']); }

    public function filters(): array
    { return [['key'=>'status','label'=>'Status','type'=>'select','options'=>TaskStatus::options()]]; }

    public function sortableColumns(): array
    { return ['title','status','due_date','created_at']; }

    public function columnMap(): array          // key => ['label'=>..., 'value'=>fn($row)=>scalar]
    {
        return [
            'title'  => ['label'=>'Title',  'value'=>fn (Task $t) => $t->title],
            'status' => ['label'=>'Status', 'value'=>fn (Task $t) => $t->status->label()],
            // ...
        ];
    }

    public function summary(Builder $query): array { /* total / in progress / done counts */ }
}
```

The base `applyFilters()` applies the shared **date range** (`date_from` /
`date_to` against `dateColumn()`, default `created_at`) and a **status equality
filter** when the report exposes one — reused by listing, summary, and export so
every surface stays consistent.

### The registry — one line per report

```php
// app/Support/Reports/ReportRegistry.php
private static array $map = [
    'projects' => ProjectsReport::class,
    'tasks'    => TasksReport::class,
];
```

`ReportRegistry::all()` instantiates them all; `get($key)` resolves one or 404s.
**No service provider, no config** — just this array.

### One controller serves every panel

`ReportController` is surface-agnostic: it derives the layout and child route
prefix from the current route name (`tenant.*` / `admin.*` / `member.*`):

```php
// app/Http/Controllers/ReportController.php
private function prefix(Request $r): string
{ return explode('.', (string) $r->route()?->getName())[0] ?: 'tenant'; }

private function layout(Request $r): string
{ return $this->prefix($r) === 'member' ? 'layouts.member-portal' : 'layouts.app'; }
```

It exposes `index` (list reports), `show` (one report page), `listing`
(DataTable JSON via `ReportsRepository`), and `export`.

### Filtered DataTable + matching export

`ReportsRepository::datatable()` applies the definition's filters then runs the
**same shared `DataTableBuilder`** every CRUD listing uses (docs/06), mapping
rows through `columnMap()`:

```php
// app/Repositories/ReportsRepository.php
$query = $definition->applyFilters($definition->baseQuery(), $request);
return DataTableBuilder::for($query, $request)
    ->searchable($this->searchable($definition))   // only real DB columns
    ->orderable($definition->sortableColumns())
    ->map(fn (object $row) => $definition->mapRow($row))
    ->toArray();
```

`ReportExport` (maatwebsite/excel, `FromQuery + WithHeadings + WithMapping`)
reuses the **same base query, filters, and column map**, so the `.xlsx` always
matches the on-screen filtered view:

```php
// app/Exports/ReportExport.php
public function query(): Builder
{ return $this->definition->applyFilters($this->definition->baseQuery(), new Request($this->filters)); }
public function headings(): array { return array_values($this->definition->headings()); }
public function map($row): array  { return array_values($this->definition->mapRow($row)); }
```

The route then streams it: `Excel::download(new ReportExport($definition, $request->all()), "{$report}.xlsx")`.

---

## How to extend — add a report

1. Create `app/Reports/InvoicesReport.php` extending `ReportDefinition`. Implement
   `key()`, `label()`, `baseQuery()` (org-scoped), `columnMap()`; optionally
   override `filters()`, `sortableColumns()`, `summary()`, `dateColumn()`.
2. Add **one line** to `ReportRegistry::$map`:
   `'invoices' => InvoicesReport::class`.

It now appears in every panel's reports list with a filtered DataTable and Excel
export. No controller, route, or provider changes needed (the report routes
already exist under each surface's `reports.*` group).

To add a dashboard widget, extend `TenantDashboardService::build()` and render it
in `resources/views/tenant/dashboard.blade.php`.

---

## Gotchas

- **Reports inherit org isolation only through `baseQuery()`** — always start
  from an org-scoped model query (`Task::query()`), never `DB::table()`.
- Only **real DB columns** in `sortableColumns()` are searchable/orderable;
  computed/relation columns (e.g. `assignee` name) are excluded from free-text
  search by `ReportsRepository::searchable()`.
- The export reuses `applyFilters()`, so it honours the *current* filters passed
  via query string — make sure the front-end forwards `date_from`/`date_to`/`status`
  to the export URL.
- The admin dashboard deliberately has **no Lead dependency** — leads are a
  separate central surface (see [08](08-public-landing-and-leads.md)).
