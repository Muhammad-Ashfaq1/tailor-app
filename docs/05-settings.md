# 05 — Organization Settings

Per-organization settings stored as a JSON column, exposed through **route-based
settings tabs** (each an AJAX form), plus per-org currency formatting (`@money`).

See also: [Tenancy](01-tenancy.md) · [CRUD module convention](06-crud-module-convention.md) ·
[Frontend](10-frontend-assets-and-pwa.md)

---

## Overview

Each organization has a `settings` JSON column. The shape is defined by
`OrganizationSettings::DEFAULT_SETTINGS`; stored values are **deep-merged over
the defaults on read**, so adding a new default key works for existing tenants
**without a migration**. This one JSON column is the flexible store for the whole
settings area (profile, regional, currency, operations, loyalty, invoice,
notifications).

The UI is a **settings sub-nav + content** (Settings is pinned to the bottom of
the main sidebar). Each tab is one **AJAX** form: it POSTs via axios, the server
returns `{message}` JSON, and the client shows a `notyf` toast (422 → inline
`.invalid-feedback` per field). The scalar forms are driven by a declarative
`SettingsSchema`; the two non-scalar forms have their own request.

---

## Key files

| File | Responsibility |
| --- | --- |
| `app/Support/Settings/OrganizationSettings.php` | Default settings tree + deep-merge. |
| `app/Support/Settings/SettingsSchema.php` | `PAGES`, scalar `SECTIONS`, `NOTIFICATION_EVENTS`, and the field → rules/path/cast schema. |
| `app/Support/Settings/SettingsOptions.php` | Dropdown option lists (locales, timezones, date/time formats, …). |
| `app/Http/Requests/SaveSettingsRequest.php` | Rules for a scalar section, derived from the schema. |
| `app/Http/Requests/SaveNotificationsRequest.php` | Rules for the per-event notification matrix. |
| `app/Http/Controllers/Tenant/SettingController.php` | `index(page)` + `save(section)` + `saveNotifications()` (JSON). |
| `app/Actions/Settings/UpdateOrganizationSettings.php` | Apply dot-path updates (+ optional org name) to the JSON. |
| `app/Support/Currency.php` | Per-org currency formatting + JS config. |
| `resources/views/tenant/settings/index.blade.php` | The **tab list ($tabs, defined here)** + active tab. |
| `resources/views/tenant/settings/partials/*.blade.php` | One partial per tab. |
| `public/organization/js/settings/settings.js` | Generic AJAX submit for every `form.settings-form`. |

---

## How it works

### 1. Defaults + deep-merge

`OrganizationSettings::DEFAULT_SETTINGS` holds the full tree (profile, regional,
operations, loyalty, invoice, notifications). Reads merge stored over defaults:

```php
// app/Models/Organization.php
public function mergedSettings(): array
{
    return OrganizationSettings::merged($this->settings ?? []);
}

public function setting(string $key, mixed $default = null): mixed
{
    return data_get($this->mergedSettings(), $key, $default); // e.g. 'regional.currency'
}
```

### 2. Pages vs sections

`SettingsSchema` separates the two:

```php
// app/Support/Settings/SettingsSchema.php
public const PAGES    = ['profile','regional','operations','notifications','invoice','roles'];
public const SECTIONS = ['profile','regional','operations','loyalty','invoice']; // scalar, schema-driven saves
public const NOTIFICATION_EVENTS = ['order_placed' => 'Order placed', /* … */];
```

- **PAGES** are the route-based tabs (`GET /tenant/settings/{page?}`). `roles`
  is a link out to the existing roles screen; it has no form.
- **SECTIONS** are the scalar forms saved via `POST /tenant/settings/save/{section}`.
  The **General-ish** tabs map a page to a section; the *Notifications & Loyalty*
  page renders **two** forms (the loyalty scalar section + the notification matrix).

### 3. The schema is the single source of truth for scalar forms

Each field maps a form input name to its rules, the **dot-path** where it lands
in the JSON, and an optional cast. The special path `@name` writes the org's
`name` column instead of the JSON:

```php
'profile' => [
    'shop_name'     => ['rules' => ['required','string','max:255'], 'path' => '@name'],
    'business_name' => ['rules' => ['required','string','max:255'], 'path' => 'profile.business_name'],
    // …
],
'regional' => [
    'locale'            => ['rules' => ['required','in:en,ar'], 'path' => 'regional.locale'],
    'currency'          => ['rules' => ['required','string','size:3'], 'path' => 'regional.currency'],
    'currency_decimals' => ['rules' => ['required','integer','min:0','max:4'], 'path' => 'regional.currency_decimals', 'cast' => 'int'],
    // …
],
```

`regional.locale` (`en`/`ar`) drives the whole UI language + RTL — the
`set.organization.locale` middleware reads it per request. `regional.direction`
is stored for completeness but is always derived from the locale at runtime. See
[11 — Localization & RTL](11-localization-and-rtl.md).

`SaveSettingsRequest` derives its rules from the section (`$this->route('section')`),
and `SettingController::save()` casts each value, routes `@name` to the org name,
collects the rest as dot-updates, and hands them to the action — returning JSON:

```php
// SettingController::save()  → response()->json(['message' => 'Settings saved.'])
foreach (SettingsSchema::section($section) as $field => $def) {
    $value = $this->castValue($validated[$field] ?? null, $def['cast'] ?? null);
    if ($def['path'] === '@name') { $name = (string) $value; continue; }
    $dotUpdates[$def['path']] = $value;
}
$this->updateSettings->handle($organization, $dotUpdates, $name);
```

```php
// app/Actions/Settings/UpdateOrganizationSettings.php
foreach ($dotUpdates as $path => $value) {
    data_set($settings, $path, $value); // e.g. data_set($s, 'regional.currency', 'SAR')
}
```

### 4. Notifications — a per-event matrix (not scalar)

The *Notifications & Loyalty* page renders a table of events × channels
(email / in-app). It's an array payload, so it has its own request + save method:

```php
// SaveNotificationsRequest — rules built from SettingsSchema::NOTIFICATION_EVENTS
'events.order_placed.email'  => ['boolean'],
'events.order_placed.in_app' => ['boolean'],
// SettingController::saveNotifications() → notifications.events.{event}.{email|in_app}
```

Each checkbox has a hidden `value="0"` sibling (bracket-named, e.g.
`events[order_placed][email]`) so an unchecked box still posts `0`.

### 5. The tab list lives in the Blade

The sub-nav (labels + icons) is defined in the **view**, not the controller — the
controller only validates the page key (`SettingsSchema::isValidPage()`):

```blade
{{-- resources/views/tenant/settings/index.blade.php --}}
@php $tabs = [
    'profile'       => ['label' => 'Shop Profile',           'icon' => 'tabler-building-store'],
    'regional'      => ['label' => 'Regional & Billing',     'icon' => 'tabler-world'],
    'operations'    => ['label' => 'Operations',             'icon' => 'tabler-settings'],
    'notifications' => ['label' => 'Notifications & Loyalty', 'icon' => 'tabler-bell'],
    'invoice'       => ['label' => 'Order & Invoice',        'icon' => 'tabler-receipt'],
    'roles'         => ['label' => 'Roles & Permissions',    'icon' => 'tabler-shield-lock'],
]; @endphp
@include('tenant.settings.partials.'.$page)
```

### 6. AJAX submit + notyf (settings.js)

`public/organization/js/settings/settings.js` submits every `form.settings-form`
as `FormData` via axios (so nested notification names post correctly), shows a
`notyf` toast on success, maps `422` errors inline (`.invalid-feedback[data-field]`),
and disables the submit button while saving. Because settings post via axios,
they rely on the JSON-error exception config (see [routing](02-routing-and-middleware.md)).

### 7. Per-organization currency

`Currency::config()` reads the current org's regional settings (currency, symbol,
position, **decimals**, thousands/decimal **separators**), memoises them, and
backs both the `@money` Blade directive and `window.appCurrency` /
`window.formatMoney()` (see [frontend](10-frontend-assets-and-pwa.md)). Currency
fields live under `regional.*` on purpose, so editing them via the *Regional &
Billing* tab updates `@money` everywhere. Defaults are **SAR / Asia-Riyadh**.

```php
Currency::format(1234.5); // "1,234.50 SAR" or "SAR 1,234.50" by org settings
```

---

## How to extend

### Add a scalar setting
1. Add the default value to `OrganizationSettings::DEFAULT_SETTINGS`.
2. Add the field to its section in `SettingsSchema::all()` with `rules`, `path`, `cast`.
3. Add the input to the section's partial (with a `.invalid-feedback[data-field="…"]`).

Validation and persistence are picked up automatically.

### Add a whole tab
1. Add the key to `SettingsSchema::PAGES` (and a scalar `SECTIONS` entry, or a
   custom save method for non-scalar forms).
2. Add a `resources/views/tenant/settings/partials/<page>.blade.php` with a
   `form.settings-form` posting to `tenant.settings.save` (or a custom route).
3. Add a `$tabs` entry (label + icon) in `index.blade.php`.

### Add a notification event
Add it to `OrganizationSettings::DEFAULT_SETTINGS['notifications']['events']` and
`SettingsSchema::NOTIFICATION_EVENTS` — the matrix and its request pick it up.

---

## Gotchas

- **The tab list is in the Blade** (`$tabs` in `index.blade.php`); the controller
  only validates the page key. Keep the two page-key sets in sync.
- **`@name` is special-cased** — it updates `organizations.name`, not the JSON.
  Don't point two fields at `@name`.
- **Notifications is an array form**, not scalar schema — it has its own
  `SaveNotificationsRequest` + `saveNotifications()`. Checkbox fields need the
  hidden-`0` fallback or an unchecked box posts nothing.
- **Currency fields stay under `regional.*`** so `Currency::config()` keeps
  finding them. `Currency::config()` is memoised per request — call
  `Currency::flush()` after switching tenant in a long-running worker.
- **Settings forms are AJAX**, so they depend on the JSON-error exception config
  (`shouldRenderJsonWhen(... || expectsJson())`) to receive `422` — see
  [02 — Routing & Middleware](02-routing-and-middleware.md).
- `settings.manage` is enforced at the route group and in each form request.
