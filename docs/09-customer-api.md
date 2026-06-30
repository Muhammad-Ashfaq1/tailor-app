# 09 — Customer API (Sanctum)

A stateless, org-scoped JSON API at `/api/v1/*` for customers, authenticated
with Sanctum Bearer tokens against a **separate guard**.

See also: [Tenancy](01-tenancy.md) · [Routing & middleware](02-routing-and-middleware.md) ·
[CRUD module convention](06-crud-module-convention.md)

---

## Overview

The API is for **customers** (a distinct identity from web `User`s). Login carries
the **org slug** in the body, resolves the tenant, and issues a token. Subsequent
requests initialise tenancy from the token holder, so the same
`BelongsToOrganization` global scope isolates data exactly as on the web.

```
POST /api/v1/login {organization, email, password}
   → resolve org by slug → tenancy()->initialize($org) → match Customer → issue token
Bearer token → auth:sanctum → customer.org.init (tenancy) → org-scoped reads/writes
```

---

## Key files

| File | Responsibility |
| --- | --- |
| `routes/api.php` | `/api/v1/*` routes. |
| `config/auth.php` | `customer` guard + `customers` provider. |
| `config/sanctum.php` | `expiration => null` (tokens don't expire). |
| `app/Models/Customer.php` | Org-scoped Sanctum identity (`HasApiTokens` + `BelongsToOrganization`). |
| `app/Http/Middleware/InitializeTenancyFromCustomer.php` | `customer.org.init`. |
| `app/Http/Controllers/Api/V1/AuthController.php` | login / me / logout. |
| `app/Http/Controllers/Api/V1/ProjectController.php` | Project reads. |
| `app/Http/Controllers/Api/V1/TaskController.php` | Task list / create / status. |
| `app/Http/Requests/Api/CustomerLoginRequest.php` | Login validation. |
| `app/Http/Resources/Api/{Customer,Project,Task}Resource.php` | JSON shaping. |
| `database/migrations/2026_06_30_120001_create_customers_table.php` | `customers` table. |

---

## How it works

### 1. A separate guard + provider

```php
// config/auth.php
'guards' => [
    'web'      => ['driver' => 'session', 'provider' => 'users'],
    'customer' => ['driver' => 'sanctum', 'provider' => 'customers'],
],
'providers' => [
    'users'     => ['driver' => 'eloquent', 'model' => User::class],
    'customers' => ['driver' => 'eloquent', 'model' => Customer::class],
],
```

`config/sanctum.php` sets `'expiration' => null` — tokens live until revoked.

### 2. The Customer is org-scoped like any model

```php
// app/Models/Customer.php
class Customer extends Authenticatable
{
    use BelongsToOrganization;  // same global org scope as Project/Task
    use HasApiTokens;           // Sanctum tokens
    use SoftDeletes;
    // ...
}
```

The migration scopes email **per org** (`unique(['organization_id','email'])`),
so the same email can exist in two organizations.

### 3. Login resolves the tenant from the slug

Because no tenant is set yet at login, the org lookup runs **without** the org
scope, then tenancy is initialised so the `Customer` lookup auto-scopes:

```php
// app/Http/Controllers/Api/V1/AuthController.php
$organization = Organization::query()->where('slug', $request->string('organization'))->first();
if ($organization === null || ! $organization->isApproved()) $this->failInvalidCredentials();

tenancy()->initialize($organization);   // now Customer queries are org-scoped

$customer = Customer::query()->where('email', $request->string('email'))->first();
if ($customer === null || ! $customer->is_active
    || ! Hash::check((string) $request->string('password'), $customer->password)) {
    $this->failInvalidCredentials();     // generic message — no user enumeration
}

$token = $customer->createToken('api')->plainTextToken;
return response()->json(['token' => $token, 'customer' => [...], 'organization' => [...]]);
```

### 4. Authenticated requests boot tenancy from the token holder

```php
// routes/api.php
Route::middleware(['auth:sanctum', 'customer.org.init'])->group(function () {
    Route::get('/me',     [AuthController::class, 'me']);
    Route::post('/logout',[AuthController::class, 'logout']);
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);
    Route::get('/tasks',  [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus']);
});
```

`customer.org.init` is the API analogue of `org.init`:

```php
// app/Http/Middleware/InitializeTenancyFromCustomer.php
if ($customer !== null && $customer->organization_id !== null && $customer->organization !== null) {
    tenancy()->initialize($customer->organization);
}
```

### 5. Controllers are thin and rely on the scope

Tenancy is already initialised, so queries and creates auto-scope; route-model
binding is org-scoped too:

```php
// app/Http/Controllers/Api/V1/ProjectController.php
public function index(): AnonymousResourceCollection
{ return ProjectResource::collection(Project::query()->latest()->paginate(15)); }

public function show(Project $project): ProjectResource
{ return new ProjectResource($project); }   // binding is org-scoped → 404 cross-tenant
```

```php
// app/Http/Controllers/Api/V1/TaskController.php — store
$validated = $request->validate([
    'title' => ['required','string','max:255'],
    'project_id' => ['required',
        Rule::exists((new Project)->getTable(), 'id')
            ->where('organization_id', $request->user()->organization_id)],  // org-scoped FK
    'status' => ['nullable', Rule::enum(TaskStatus::class)],
]);
$task = Task::create([...]);   // organization_id auto-fills via the trait
```

Responses go through `*Resource` classes for consistent JSON. `login` is throttled
`5,1`; API exceptions render as JSON (`shouldRenderJsonWhen(api/*)` in `bootstrap/app.php`).

---

## How to extend

- **Expose another resource:** add an `Api/V1/<Resource>Controller`, a
  `Resource` class, and routes under the `auth:sanctum` + `customer.org.init`
  group. The org scope handles isolation; org-scope any FK with
  `Rule::exists()->where('organization_id', ...)`.
- **Seed API customers:** use `CustomerFactory` (has `forOrganization()` /
  `inactive()` states); the demo seeder already creates a few.

---

## Gotchas

- **Login must NOT be org-scoped on the org lookup** — the slug comes from the
  request body before any tenant exists; that lookup uses the default (unscoped)
  query, then `tenancy()->initialize()`.
- Use the **`customer` guard**, never `web`, for API auth — they resolve
  different models.
- Login returns a **generic** "Invalid credentials" for every failure (wrong org,
  inactive, bad password) to avoid user/tenant enumeration.
- Tokens **don't expire** (`expiration => null`) — `logout` revokes the current
  token via `currentAccessToken()->delete()`.
- Customer email is unique **per org**, not globally — always resolve within the
  org context.
