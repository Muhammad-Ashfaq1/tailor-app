# 05 — Organization Settings

Per-organization settings stored as a JSON column, exposed through a
schema-driven settings UI, plus per-org currency formatting (`@money`).

See also: [Tenancy](01-tenancy.md) · [CRUD module convention](06-crud-module-convention.md)

---

## Overview

Each organization has a `settings` JSON column. The shape is defined by
`OrganizationSettings::DEFAULT_SETTINGS`; stored values are **deep-merged over
the defaults on read**, so adding a new default key works for existing tenants
**without a migration**.

The settings *form* is driven by a second, declarative class `SettingsSchema`,
read by both the form request (for validation rules) and the controller (for
persistence). Adding a setting field is a one-line change there.

---

## Key files

| File | Responsibility |
| --- | --- |
| `app/Support/Settings/OrganizationSettings.php` | Default settings tree + deep-merge. |
| `app/Support/Settings/SettingsSchema.php` | Form schema: field → rules, dot-path, cast. |
| `app/Http/Requests/SaveSettingsRequest.php` | Rules derived from the schema. |
| `app/Http/Controllers/Tenant/SettingController.php` | Render section + persist. |
| `app/Actions/Settings/UpdateOrganizationSettings.php` | Apply dot-path updates to the JSON. |
| `app/Support/Currency.php` | Per-org currency formatting + JS config. |
| `app/Providers/AppServiceProvider.php` | Registers the `@money` Blade directive. |
| `app/Models/Organization.php` | `mergedSettings()` / `setting('dot.path')`. |
| `resources/views/tenant/settings/index.blade.php` | The settings UI. |

---

## How it works

### 1. Defaults + deep-merge

`OrganizationSettings::DEFAULT_SETTINGS` holds the full tree (regional, tax,
invoice, notifications, business_hours). Reads merge stored over defaults:

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

`deepMerge()` recurses into associative arrays and lets stored scalar/list
values win.

### 2. The form schema is the single source of truth

`SettingsSchema` maps each form field to its validation rules, the **dot-path**
where it lands in the JSON, and an optional cast. The special path `@name`
writes to the org's `name` column instead of the JSON:

```php
// app/Support/Settings/SettingsSchema.php
'regional' => [
    'currency'          => ['rules' => ['required','string','size:3'], 'path' => 'regional.currency'],
    'currency_position' => ['rules' => ['required','in:before,after'], 'path' => 'regional.currency_position'],
    'timezone'          => ['rules' => ['required','timezone'],        'path' => 'regional.timezone'],
],
'operations' => [
    'tax_enabled' => ['rules' => ['boolean'], 'path' => 'tax.enabled', 'cast' => 'bool'],
    'tax_rate'    => ['rules' => ['numeric','min:0','max:100'], 'path' => 'tax.rate', 'cast' => 'float'],
],
```

`SECTIONS = ['general','regional','operations','notifications']`.

### 3. Validation derives from the schema

```php
// app/Http/Requests/SaveSettingsRequest.php
public function authorize(): bool { return (bool) $this->user()?->can('settings.manage'); }

public function rules(): array
{
    $section = (string) $this->route('section');           // 404 if unknown
    $rules = [];
    foreach (SettingsSchema::section($section) as $field => $def) {
        $rules[$field] = $def['rules'];
    }
    return $rules;
}
```

### 4. Persistence: cast + collect dot-updates + apply

The controller casts each value, routes `@name` to the org name, collects the
rest as dot-path updates, and hands them to the action:

```php
// SettingController::save()
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
    data_set($settings, $path, $value); // e.g. data_set($s, 'regional.currency', 'EUR')
}
$organization->settings = $settings;
```

Checkbox fields use the hidden-input-then-checkbox trick in Blade so an
unchecked box still posts `0`.

### 5. Per-organization currency

`Currency::config()` reads the current org's regional settings (falling back to
defaults in central context) and memoises them. It backs:

- the **`@money` Blade directive**, registered in `AppServiceProvider::boot()`:

  ```php
  Blade::directive('money', fn (string $e) =>
      "<?php echo \App\Support\Currency::format($e); ?>");
  ```

- **`window.appCurrency`** for JS — the layout head injects
  `@json(\App\Support\Currency::jsConfig())`, and `public/organization/js/app.js`
  exposes a matching `window.formatMoney()` (see [frontend](10-frontend-assets-and-pwa.md)).

```php
// app/Support/Currency.php
Currency::format(1234.5); // "$1,234.50" or "1,234.50$" by org settings
```

---

## How to extend — add a setting

1. Add the default value to `OrganizationSettings::DEFAULT_SETTINGS` (so it
   appears for existing tenants without a migration).
2. Add the field to the appropriate section in `SettingsSchema::all()` with its
   `rules`, `path`, and optional `cast`.
3. Add the input to `resources/views/tenant/settings/index.blade.php` in that
   section.

That's the whole change — validation and persistence pick it up automatically.

To add a whole new **section**, add its name to `SettingsSchema::SECTIONS` and an
entry in `all()`, plus a Blade branch.

---

## Gotchas

- **`Currency::config()` is memoised** per request. In a long-running worker that
  switches tenants, call `Currency::flush()` after switching.
- The `@name` path is special-cased — it updates `organizations.name`, not the
  JSON. Don't point two fields at `@name`.
- Booleans must be cast (`'cast' => 'bool'`) and need the hidden-input fallback
  in Blade, or an unchecked box posts nothing and the value is lost.
- `settings.manage` is enforced both at the route group and in the form request.
