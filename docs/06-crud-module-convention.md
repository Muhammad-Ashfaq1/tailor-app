# 06 — CRUD Module Convention

**The most important doc for day-to-day work.** Every tenant resource follows
the same vertical slice. This explains it end-to-end using the live **Members**
module as the worked example (with the central **Leads** module to show the
repository pattern), then gives a step-by-step recipe for adding a new resource
module.

See also: [Tenancy](01-tenancy.md) · [RBAC](03-rbac.md) ·
[Routing & middleware](02-routing-and-middleware.md) · [Frontend](10-frontend-assets-and-pwa.md)

---

## The anatomy of a module

A resource module is one vertical slice across these layers. Not every layer is
mandatory — Members is org-scoped users with no slug/enum, while a slugged
resource adds an enum, a repository and `HandlesSlugs`:

```
Migration  →  Model (+ Enum)  →  Repository (Interface + impl)  →  FormRequest
           →  Controller  →  Routes (permissions)  →  Blade (listing + save modal)
           +  Factory  +  Permissions in the catalog
```

| Layer | Members example | Responsibility |
| --- | --- | --- |
| Model | `app/Models/User.php` | `organization_id` ownership; members are users sharing the current org. |
| DataTable | `app/Support/DataTables/DataTableBuilder.php` | Reusable server-side DataTables responder. |
| FormRequest | `app/Http/Requests/SaveMemberRequest.php` | Validation + `authorize()` (create/update split). |
| Controller | `app/Http/Controllers/Tenant/MemberController.php` | Thin: request → query/builder → Blade/JSON. |
| Routes | `routes/tenant.php` | `index`/`listing`/`save` (+ `impersonate`) + permission middleware. |
| Blade | `resources/views/tenant/members/index.blade.php` | Listing table + single save modal. |
| Factory | `database/factories/UserFactory.php` | Seed/test data. |

A slugged resource that owns its own table additionally uses a repository plus
shared infra:

| Layer | Repository-backed example | Responsibility |
| --- | --- | --- |
| Interface | `app/Repositories/Interface/LeadRepositoryInterface.php` | The repo contract. |
| Repository | `app/Repositories/LeadRepository.php` | `datatable()`, `create()`/`save()`, status updates. |
| Base repo | `app/Repositories/BaseRepository.php` | `withAudit()` stamps `created_by`/`updated_by`. |
| Slugs | `app/Repositories/Concerns/HandlesSlugs.php` | Per-org unique slugs (`generateUniqueSlug()`). |

---

## How it works, layer by layer

### Model — org-scoped

Members are `User` rows sharing the current `organization_id`; the
`BelongsToOrganization` trait (see [docs/01](01-tenancy.md)) supplies org
isolation, auto-fills `organization_id` on create, and makes route-model
binding org-safe. A resource that owns its own table looks the same:

```php
// a hypothetical slugged resource — app/Models/Widget.php
class Widget extends Model
{
    use BelongsToOrganization;   // org isolation (see docs/01)
    use FiltersByDateRange;      // dateRange() scope
    use HasFactory; use SoftDeletes;

    protected $fillable = ['name','slug','status','description','created_by','updated_by'];
    protected function casts(): array { return ['status' => WidgetStatus::class]; }
    public function getRouteKeyName(): string { return 'slug'; }   // /widgets/{slug}
}
```

`organization_id` is **not** in `$fillable` — it's auto-filled by the trait and
the FormRequest **prohibits** it from the client.

### Controller listing — the DataTable shape

Members build the server-side payload directly in the controller via the shared
`DataTableBuilder`; the org scope is applied explicitly here because `User` is
queried by `organization_id`:

```php
// app/Http/Controllers/Tenant/MemberController.php
public function listing(Request $request): JsonResponse
{
    $query = User::query()->where('organization_id', OrganizationContext::id());

    return response()->json(
        DataTableBuilder::for($query, $request)
            ->searchable(['name', 'email'])
            ->orderable(['id', 'name', 'email', 'role', 'created_at'])
            ->map(fn (User $user): array => [
                'id' => $user->id, 'name' => $user->name, 'email' => $user->email,
                'role' => $user->role, 'role_label' => Str::headline((string) $user->role),
                'is_active' => $user->is_active, 'is_self' => $user->id === auth()->id(),
            ])
            ->toArray()
    );
}
```

### Repository — owns persistence + listing shape

For a resource that owns its own table, persistence and listing shape live in a
repository bound interface→concrete in `AppServiceProvider::register()`:

```php
private array $repositories = [
    LeadRepositoryInterface::class => LeadRepository::class,
];
// foreach: $this->app->bind($interface, $concrete);
```

`datatable()` builds the server-side payload via the shared `DataTableBuilder`.
For an org-owned model the org scope is automatic (no `where organization_id`
needed); `Lead` is central, so its query is deliberately unscoped:

```php
// app/Repositories/LeadRepository.php
public function datatable(Request $request): array
{
    $query = Lead::query();   // central resource — no global org scope here
    return DataTableBuilder::for($query, $request)
        ->searchable(['name', 'email', 'company'])
        ->orderable(['id', 'status', 'created_at'])
        ->map(fn (Lead $lead) => [
            'id' => $lead->id, 'name' => $lead->name, 'email' => $lead->email,
            'status' => $lead->status->value, 'status_label' => $lead->status->label(),
            'status_color' => $lead->status->color(),
            'created_at' => $lead->created_at?->toDateString(),
        ])->toArray();
}
```

For an org-owned, slugged resource, a `save()` that handles **both create and
update** (id present → update) regenerates the slug from the name only when it
changes, and stamps audit columns through the base repository:

```php
// pattern for a slugged, org-owned resource
public function save(array $data, ?int $id = null): Widget
{
    $creating = $id === null;
    $widget = $creating ? new Widget : $this->find($id);
    if ($widget === null) abort(404);
    if ($creating || ($data['name'] ?? null) !== $widget->name) {
        $data['slug'] = $this->generateUniqueSlug(Widget::class, $data['name'], $creating ? null : $widget->id);
    }
    $widget->fill($this->withAudit($data, $creating))->save();
    return $widget->refresh();
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
// app/Http/Requests/SaveMemberRequest.php
public function authorize(): bool
{
    return $this->boolean('_is_update')
        ? (bool) $this->user()?->can('members.update')
        : (bool) $this->user()?->can('members.create');
}
protected function prepareForValidation(): void { $this->merge(['_is_update' => $this->filled('id')]); }

public function rules(): array
{
    $id = $this->filled('id') ? (int) $this->input('id') : null;
    return [
        'id'    => ['nullable','integer'],
        'name'  => ['required','string','max:255'],
        'email' => ['required','email','max:255', Rule::unique('users','email')->ignore($id)],
        'role'  => ['required', Rule::in(User::TENANT_ROLES)],
        'is_active' => ['boolean'],
        'password'  => [$id === null ? 'required' : 'nullable', 'confirmed', Password::defaults()],
        'organization_id' => ['prohibited'],   // server-set columns never from client
    ];
}
```

**Cross-tenant FK guard:** for any foreign key that references another tenant
resource, use `Rule::exists(...)->where('organization_id', $orgId)` so you can't
attach to another tenant's rows.

### Controller — thin

```php
// app/Http/Controllers/Tenant/MemberController.php
public function index(): View
{ return view('tenant.members.index', ['roles' => User::TENANT_ROLES]); }

public function save(SaveMemberRequest $request): JsonResponse
{
    $orgId = (int) OrganizationContext::id();
    $id = $request->filled('id') ? (int) $request->input('id') : null;

    $user = $id !== null
        ? User::where('organization_id', $orgId)->findOrFail($id)
        : new User(['organization_id' => $orgId]);

    $user->name = $request->string('name')->toString();
    // ... assign the rest, then:
    $user->save();
    $user->assignPrimaryRole($request->string('role')->toString());

    return response()->json(['message' => 'Member saved.', 'member' => ['id' => $user->id]]);
}
```

For a resource that owns its own table, route-model binding (`Widget $widget`)
is **org-scoped** (docs/01), so a foreign id 404s, and `destroy` simply deletes
through the repository.

### Routes — see [routing](02-routing-and-middleware.md)

`index`+`listing` share `permission:members.view`; `save` uses
`permission:members.create|members.update`; `impersonate` uses
`members.impersonate`. A typical CRUD resource adds `destroy` under a
`<resource>.delete` permission.

### Blade — listing table + one save modal

`resources/views/tenant/members/index.blade.php` is the canonical pattern:

- A `<table>` hydrated by a server-side
  `$('#members-table').DataTable({ serverSide:true, ajax:{ url: listingUrl } })`.
- **One** save modal form used for both create and edit — a hidden `id`
  input decides. "New" clears it; the edit button GETs `show` (or reads the row)
  and fills it.
- `axios.post(saveUrl, ...)` submits; **422** responses paint inline
  `.invalid-feedback` per field; success reloads the table and shows a SweetAlert.
- Buttons gated with `@can('members.create')` and JS flags
  (`canUpdate`/`canDelete` from `auth()->user()->can(...)`).
- Modal fields use the shared `<x-form.input|select|textarea|password|switch>`
  components (label + control + `.invalid-feedback` in one tag) and all visible
  text is `{{ __('module.key') }}`. See [11 — Localization & RTL](11-localization-and-rtl.md)
  for the components and the JS string bridge.

The JS libs (jQuery, DataTables, Select2, SweetAlert2, axios) are **static
files** under `public/organization/` — no build step (see [10](10-frontend-assets-and-pwa.md)).

### Forcing a scope server-side

When a surface must restrict rows to the current user (e.g. an employee seeing
only their own records), force the scope **server-side** in the controller —
never trust a client flag:

```php
public function listing(Request $request): JsonResponse
{
    $request->merge(['mine' => true]);   // ignore client input, force the scope
    // ... build the DataTable from the now-forced filter
}
```

---

## Recipe — add a new resource module ("Widgets")

1. **Permissions:** add `'widgets' => ['view','create','update','delete']` to
   `PermissionCatalog::RESOURCES`, grant in `tenantMatrix()`, run
   `php artisan permissions:sync`, and re-provision org roles (see [RBAC](03-rbac.md)).
2. **Migration:** create `widgets` with `foreignId('organization_id')->constrained()->cascadeOnDelete()`,
   your columns, `created_by`/`updated_by` nullable FKs, `timestamps()`,
   `softDeletes()`, and `unique(['organization_id','slug'])` if it has a slug.
3. **Enum** (optional): `app/Enums/WidgetStatus.php` with `label()/color()/options()`.
4. **Model:** `app/Models/Widget.php` with `use BelongsToOrganization;` (+
   `FiltersByDateRange`), `$fillable` (no `organization_id`), enum casts,
   `getRouteKeyName()` if slug-routed, relations.
5. **Interface + Repository:** mirror `LeadRepositoryInterface` /
   `LeadRepository` (`datatable`, `save`/`create`, `find`, `delete`, dropdown
   methods). Extend `BaseRepository`; add `HandlesSlugs` if slugged.
6. **Bind** it: add `WidgetRepositoryInterface::class => WidgetRepository::class`
   to `$repositories` in `AppServiceProvider`.
7. **FormRequest:** `SaveWidgetRequest` with `authorize()` (create/update split,
   like `SaveMemberRequest`), `rules()` (prohibit `organization_id`/`created_by`/
   `updated_by`; org-scope any FK with `Rule::exists()->where('organization_id', $orgId)`),
   and a `payload()`/`safe()` accessor.
8. **Authorization:** the FormRequest's `authorize()` covers create/update; gate
   destructive actions in the controller (e.g. `$this->user()->can('widgets.delete')`).
   Add a policy + register it in `AuthServiceProvider::$policies` only if you need
   model-level checks.
9. **Controller:** `Tenant/WidgetController` — `index`/`listing`/`show`/`save`/
   `destroy` (+ `dropdowns/*`), injecting the interface.
10. **Routes:** add the controller group to `routes/tenant.php` with
    `permission:widgets.view` on `index`/`listing`/`show`,
    `permission:widgets.create|widgets.update` on `save`,
    `permission:widgets.delete` on `destroy`.
11. **Blade:** copy `tenant/members/index.blade.php` → `tenant/widgets/index.blade.php`,
    swap route names, columns, and modal fields.
12. **Factory:** `WidgetFactory` for seeding/tests; add to your demo seeder
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
- For "only mine" surfaces, force the scope **server-side**
  (`$request->merge([...])`); don't trust a client flag.
