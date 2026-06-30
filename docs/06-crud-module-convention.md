# 06 — CRUD Module Convention

**The most important doc for day-to-day work.** Every tenant resource follows
the same vertical slice. This explains it end-to-end using **Projects** (and
**Tasks**) as the worked example, then gives a step-by-step recipe for adding a
new resource module.

See also: [Tenancy](01-tenancy.md) · [RBAC](03-rbac.md) ·
[Routing & middleware](02-routing-and-middleware.md) · [Frontend](10-frontend-assets-and-pwa.md)

---

## The anatomy of a module

A resource module is one vertical slice across these layers:

```
Migration  →  Model (+ Enum)  →  Repository (Interface + impl)  →  FormRequest
           →  Controller  →  Routes (permissions)  →  Blade (listing + save modal)
           +  Factory  +  Policy  +  Permissions in the catalog
```

| Layer | Projects example | Responsibility |
| --- | --- | --- |
| Migration | `2026_06_30_110001_create_projects_table.php` | `organization_id` FK, columns, audit cols, soft deletes, **`unique(['organization_id','slug'])`**. |
| Enum | `app/Enums/ProjectStatus.php` | Status `value`/`label()`/`color()`/`options()`. |
| Model | `app/Models/Project.php` | `BelongsToOrganization` + `FiltersByDateRange`, casts, relations. |
| Interface | `app/Repositories/Interface/ProjectRepositoryInterface.php` | The repo contract. |
| Repository | `app/Repositories/ProjectRepository.php` | `datatable()`, `save()`, `find()`, `delete()`, dropdowns. |
| Base repo | `app/Repositories/BaseRepository.php` | `withAudit()` stamps `created_by`/`updated_by`. |
| Slugs | `app/Repositories/Concerns/HandlesSlugs.php` | Per-org unique slugs. |
| DataTable | `app/Support/DataTables/DataTableBuilder.php` | Reusable server-side DataTables responder. |
| FormRequest | `app/Http/Requests/SaveProjectRequest.php` | Validation + `authorize()` + `payload()`. |
| Controller | `app/Http/Controllers/Tenant/ProjectController.php` | Thin: request → repo → Blade/JSON. |
| Routes | `routes/tenant.php` | `index`/`listing`/`show`/`save`/`destroy` + permission middleware. |
| Blade | `resources/views/tenant/projects/index.blade.php` | Listing table + single save modal. |
| Policy | `app/Policies/ProjectPolicy.php` | Ability checks for `authorize()`. |
| Factory | `database/factories/ProjectFactory.php` | Seed/test data. |

---

## How it works, layer by layer

### Model — org-scoped + enum casts

```php
// app/Models/Project.php
class Project extends Model
{
    use BelongsToOrganization;   // org isolation (see docs/01)
    use FiltersByDateRange;      // dateRange() scope
    use HasFactory; use SoftDeletes;

    protected $fillable = ['name','slug','status','description','created_by','updated_by'];
    protected function casts(): array { return ['status' => ProjectStatus::class]; }
    public function getRouteKeyName(): string { return 'slug'; }   // /projects/{slug}
    public function tasks(): HasMany { return $this->hasMany(Task::class); }
}
```

`organization_id` is **not** in `$fillable` — it's auto-filled by the trait and
the FormRequest **prohibits** it from the client.

### Repository — owns persistence + listing shape

The interface is bound to the implementation in `AppServiceProvider::register()`:

```php
private array $repositories = [
    ProjectRepositoryInterface::class => ProjectRepository::class,
    TaskRepositoryInterface::class    => TaskRepository::class,
    LeadRepositoryInterface::class    => LeadRepository::class,
];
// foreach: $this->app->bind($interface, $concrete);
```

`datatable()` builds the server-side payload via the shared `DataTableBuilder`;
note the org scope is automatic (no `where organization_id`):

```php
// app/Repositories/ProjectRepository.php
public function datatable(Request $request): array
{
    $query = Project::query()->withCount('tasks');     // org scope auto-applied
    return DataTableBuilder::for($query, $request)
        ->searchable(['name', 'slug'])
        ->orderable(['id', 'name', 'status', 'created_at'])
        ->map(fn (Project $p) => [
            'id' => $p->id, 'name' => $p->name, 'slug' => $p->slug,
            'status' => $p->status->value, 'status_label' => $p->status->label(),
            'status_color' => $p->status->color(),
            'tasks_count' => $p->tasks_count, 'created_at' => $p->created_at?->toDateString(),
        ])->toArray();
}
```

`save()` handles **both create and update** (id present → update), regenerates
the slug from the name only when it changes, and stamps audit columns:

```php
public function save(array $data, ?int $id = null): Project
{
    $creating = $id === null;
    $project = $creating ? new Project : $this->find($id);
    if ($project === null) abort(404);
    if ($creating || ($data['name'] ?? null) !== $project->name) {
        $data['slug'] = $this->generateUniqueSlug(Project::class, $data['name'], $creating ? null : $project->id);
    }
    $project->fill($this->withAudit($data, $creating))->save();
    return $project->refresh();
}
```

`HandlesSlugs::generateUniqueSlug()` is **org-safe** because the uniqueness
query runs through the model's global scope (tenant-local automatically).

### DataTableBuilder — one responder for every listing

`DataTableBuilder::for($query, $request)->searchable([...])->orderable([...])->map(fn)->toArray()`
applies free-text search across whitelisted columns, **whitelisted** ordering
(prevents arbitrary column ordering), pagination, and row mapping, returning the
`{draw, recordsTotal, recordsFiltered, data}` shape DataTables expects. The same
builder powers reports (see [07](07-dashboards-and-reports.md)).

### FormRequest — authz + payload shaping

```php
// app/Http/Requests/SaveProjectRequest.php
public function authorize(): bool
{
    return $this->boolean('_is_update')
        ? (bool) $this->user()?->can('projects.update')
        : (bool) $this->user()?->can('projects.create');
}
protected function prepareForValidation(): void { $this->merge(['_is_update' => $this->filled('id')]); }

public function rules(): array
{
    return [
        'name'   => ['required','string','max:255'],
        'status' => ['required', Rule::enum(ProjectStatus::class)],
        'description' => ['nullable','string','max:5000'],
        'organization_id' => ['prohibited'],   // server-set columns never from client
        'created_by' => ['prohibited'], 'updated_by' => ['prohibited'],
    ];
}
public function payload(): array { return $this->safe()->only(['name','status','description']); }
```

**Cross-tenant FK guard** (Tasks): `project_id` / `assigned_to` use
`Rule::exists(...)->where('organization_id', $orgId)` so you can't attach to
another tenant's rows.

### Controller — thin

```php
// app/Http/Controllers/Tenant/ProjectController.php
public function __construct(private ProjectRepositoryInterface $projects) {}

public function index(): View { return view('tenant.projects.index', ['statuses' => $this->projects->statusOptions()]); }
public function listing(Request $r): JsonResponse { return response()->json($this->projects->datatable($r)); }
public function show(Project $project): JsonResponse { /* JSON for edit modal */ }
public function save(SaveProjectRequest $r): JsonResponse {
    $project = $this->projects->save($r->payload(), $r->filled('id') ? (int) $r->input('id') : null);
    return response()->json(['message' => 'Project saved.', 'project' => [...]]);
}
public function destroy(Project $project): JsonResponse {
    $this->authorize('delete', $project);  // policy
    $this->projects->delete($project);
    return response()->json(['message' => 'Project deleted.']);
}
```

Route-model binding (`Project $project`) is **org-scoped** (docs/01), so a
foreign id 404s.

### Routes — see [routing](02-routing-and-middleware.md)

`index`+`listing` share `permission:projects.view`; `save` uses
`permission:projects.create|projects.update`; `destroy` uses `projects.delete`.

### Blade — listing table + one save modal

`resources/views/tenant/projects/index.blade.php` is the canonical pattern:

- A `<table id="projects-table">` hydrated by a server-side
  `$('#projects-table').DataTable({ serverSide:true, ajax:{ url: listingUrl } })`.
- **One** `#project-modal` form used for both create and edit — a hidden `id`
  input decides. "New" clears it; the edit button GETs `show` and fills it.
- `axios.post(saveUrl, ...)` submits; **422** responses paint inline
  `.invalid-feedback` per field; success reloads the table and shows a SweetAlert.
- Buttons gated with `@can('projects.create')` and JS flags
  (`canUpdate`/`canDelete` from `auth()->user()->can(...)`).

The JS libs (jQuery, DataTables, Select2, SweetAlert2, axios) are **static
files** under `public/organization/` — no build step (see [10](10-frontend-assets-and-pwa.md)).

### The Member surface variant

The same `TaskRepository` powers the member panel, but the member controller
**forces the `mine` scope** so an employee only sees their own tasks:

```php
// app/Http/Controllers/Member/TaskController.php
public function listing(Request $request): JsonResponse
{
    $request->merge(['mine' => true]);           // ignore client input
    return response()->json($this->tasks->datatable($request));
}
public function updateStatus(UpdateTaskStatusRequest $request, Task $task): JsonResponse
{
    abort_unless($task->assigned_to === $request->user()?->id, 403, 'Not your task.');
    // ...
}
```

---

## Recipe — add a new resource module ("Invoices")

1. **Permissions:** add `'invoices' => ['view','create','update','delete']` to
   `PermissionCatalog::RESOURCES`, grant in `tenantMatrix()`, run
   `php artisan permissions:sync`, and re-provision org roles (see [RBAC](03-rbac.md)).
2. **Migration:** create `invoices` with `foreignId('organization_id')->constrained()->cascadeOnDelete()`,
   your columns, `created_by`/`updated_by` nullable FKs, `timestamps()`,
   `softDeletes()`, and `unique(['organization_id','slug'])` if it has a slug.
3. **Enum** (optional): `app/Enums/InvoiceStatus.php` with `label()/color()/options()`.
4. **Model:** `app/Models/Invoice.php` with `use BelongsToOrganization;` (+
   `FiltersByDateRange`), `$fillable` (no `organization_id`), enum casts,
   `getRouteKeyName()` if slug-routed, relations.
5. **Interface + Repository:** mirror `ProjectRepositoryInterface` /
   `ProjectRepository` (`datatable`, `save`, `find`, `delete`, dropdown methods).
   Extend `BaseRepository`; add `HandlesSlugs` if slugged.
6. **Bind** it: add `InvoiceRepositoryInterface::class => InvoiceRepository::class`
   to `$repositories` in `AppServiceProvider`.
7. **FormRequest:** `SaveInvoiceRequest` with `authorize()` (create/update split),
   `rules()` (prohibit `organization_id`/`created_by`/`updated_by`; org-scope any
   FK with `Rule::exists()->where('organization_id', $orgId)`), and `payload()`.
8. **Policy:** `InvoicePolicy` (ability checks); register it in `AuthServiceProvider::$policies`.
9. **Controller:** `Tenant/InvoiceController` — `index`/`listing`/`show`/`save`/
   `destroy` (+ `dropdowns/*`), injecting the interface.
10. **Routes:** add the controller group to `routes/tenant.php` with
    `permission:invoices.view` on `index`/`listing`/`show`,
    `permission:invoices.create|invoices.update` on `save`,
    `permission:invoices.delete` on `destroy`.
11. **Blade:** copy `tenant/projects/index.blade.php` → `tenant/invoices/index.blade.php`,
    swap route names, columns, and modal fields.
12. **Factory:** `InvoiceFactory` for seeding/tests; add to `DemoOrganizationSeeder`
    if you want demo data.

---

## Gotchas

- **Never put `organization_id` in `$fillable`** or accept it from the client —
  the trait fills it; the FormRequest prohibits it.
- The `slug` unique constraint must be **composite** (`organization_id, slug`),
  not global — two orgs can have the same slug.
- Audit columns (`created_by`/`updated_by`) are stamped by `BaseRepository::withAudit()`,
  not by the model — go through the repository's `save()`.
- `orderable()` is a **whitelist**; a column not listed won't sort (and the
  DataTable falls back to id-desc). This prevents ordering by arbitrary columns.
- Route-model binding only resolves records in the current org → expect **404**,
  not 403, on a foreign id.
- For member-style "only mine" surfaces, force the scope **server-side**
  (`$request->merge([...])`); don't trust a client flag.
