# 11 — Localization & RTL

The panel UI is bilingual: **English (`en`)** and **Saudi Arabic (`ar`)**. Each
organization picks its language in **Settings → Regional**; the whole tenant/
member UI then renders in that language, right-to-left for Arabic.

**Golden rule:** only *visible UI text* is translated. Everything internal stays
English — enum **values**, permission names, route names, DB columns, settings
keys, JS variable names. The user never sees a raw value like `in_progress` or
`walk_in`; they see a localized label.

---

## 1. Language files

Standard Laravel translation files under `lang/{en,ar}/`. Keys are snake_case
English; values are the visible label. **The `en` and `ar` file for a module
must have identical key sets** (there is a parity check — see §8).

```
lang/en|ar/
  app.php            # shared: buttons, table chrome, flash msgs, datatable.* strings
  menu.php           # sidebar + navbar labels
  settings.php       # settings tabs + field labels
  customers.php  members.php  roles.php
  dashboard.php  reports.php  leads.php  organizations.php
  auth.php           # framework auth.* keys + auth screen UI
  validation.php  pagination.php  passwords.php   # framework overrides (ar authored manually)
  # forward-looking scaffolding for modules not built yet:
  orders.php  fabrics.php  measurements.php  stitching_types.php
  payments.php  wallet.php  employees.php  expenses.php
```

The Laravel Lang package is **not** used — the `ar` framework files
(`validation`, `auth`, `pagination`, `passwords`) are authored by hand to avoid a
composer version conflict.

Use in Blade with `__()`:

```blade
<button>{{ __('orders.create') }}</button>
<th>{{ __('customers.name') }}</th>
```

---

## 2. Where the locale comes from

`regional.locale` lives in the per-org settings JSON (see [05](05-settings.md)),
allowed values `en` | `ar`. Direction is **always derived** from the locale
(`ar` → `rtl`, else `ltr`) so the two can never drift.

`app/Http/Middleware/SetOrganizationLocale.php` (alias `set.organization.locale`)
runs immediately after `org.init` on the tenant + member stacks
([02](02-routing-and-middleware.md)):

- reads `regional.locale` from the **current tenant only** (`tenant()` — never
  fires an org-scoped query),
- restricts to `en`/`ar`, falling back to `config('app.locale')`,
- `App::setLocale($locale)`,
- stashes `app_locale` + `app_direction` in the session for Blade/JS.

Super-admin / central context has no tenant, so it falls back to the app default
(English). Auth screens run before any org is known → also English by default.

---

## 3. Enums

Every enum `label()` returns a translation; **values stay English**. `options()`
therefore returns localized labels automatically.

```php
// App\Enums\CustomerType   (case WalkIn = 'walk_in')
public function label(): string
{
    return __("customers.types.{$this->value}");   // "Walk-in" / "زائر"
}
```

Label groups live in the owning module's lang file (`customers.types`,
`customers.credit_types`, `leads.status`, `organizations.status`, …). `color()`
is unchanged (Bootstrap badge colour, not user text).

---

## 4. Validation attribute names

Each FormRequest exposes `attributes()` mapping field → localized name, so
validation messages read naturally in Arabic:

```php
public function attributes(): array
{
    return ['name' => __('customers.name'), 'credit_value' => __('customers.credit_value')];
}
```

`SaveSettingsRequest` derives its attribute map from the settings schema keys.
Authorization logic is untouched; `organization_id` is still `prohibited`.

---

## 5. Layout: RTL / LTR

The layout roots (`layouts/app`, `layouts/member-portal`, `auth/layout`) set the
direction dynamically:

```blade
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
```

There is **no Vuexy RTL build** (and no build step is added). Instead a small
hand-written stylesheet, `public/organization/css/custom-rtl.css`, is loaded
**only for Arabic** (conditional `@if` in `layouts/partials/head`). It patches the
common cases: text alignment, sidebar side, dropdown alignment, form controls,
DataTables filter/pagination, Select2, and the modal close button.

---

## 6. JavaScript strings

`layouts/partials/head` exposes a global bridge (keys English, labels localized):

```js
window.AppLocale        // 'en' | 'ar'
window.AppDirection     // 'ltr' | 'rtl'
window.AppTranslations  // { datatable:{…}, confirmDelete, yesDelete, active, inactive, … }
```

`layouts/partials/scripts` then wires shared plugin defaults **once**, before any
per-page init:

```js
jQuery.extend(true, jQuery.fn.dataTable.defaults, { language: window.AppTranslations.datatable });
if (jQuery.fn.select2) jQuery.fn.select2.defaults.set('dir', window.AppDirection);
```

For page-specific JS strings (modal titles, SweetAlert, notyf literals), inject
the module's array at the top of the script and reference it:

```blade
<script>
    const T = @json(__('customers'));
    // …
    Swal.fire({ title: T.delete_confirm, confirmButtonText: window.AppTranslations.delete });
</script>
```

Server-provided messages (`notyf.success(data.message)`) are already localized in
the controller — leave them as-is. **Never** hardcode currency: use `@money()` /
`window.formatMoney()` (see [05](05-settings.md)).

---

## 7. Reusable form components (`x-form.*`)

To avoid re-typing the label + control + `.invalid-feedback` triplet in every
modal, tenant/admin forms use anonymous Blade components in
`resources/views/components/form/`:

| Component | Renders |
| --- | --- |
| `<x-form.input>` | label + `<input>` + inline feedback (default slot = help text; `<x-slot:hint>` = label suffix) |
| `<x-form.textarea>` | label + `<textarea>` + feedback |
| `<x-form.select>` | label + `<select>` (options via slot) + feedback |
| `<x-form.password>` | label + input-group-merge with eye toggle (`:feedback="false"` for confirm fields) |
| `<x-form.switch>` | hidden `0` fallback + checkbox switch |

They preserve the exact Vuexy markup — pass `id`, `name`, and any extra
attributes; the AJAX JS (which keys off `id` / `name` / `data-field`) keeps
working unchanged. Example:

```blade
<x-form.input name="name" id="customer-name" :label="__('customers.name')" wrapper="col-md-6 mb-3" required />
<x-form.select name="type" id="customer-type" :label="__('customers.type')" wrapper="col-md-6 mb-3">
    @foreach ($types as $type)
        <option value="{{ $type['value'] }}">{{ $type['label'] }}</option>
    @endforeach
</x-form.select>
```

`data-field` defaults to `name` (override with `:field`). `wrapper` sets the
column/spacing classes (default `mb-3`).

---

## 8. Adding / verifying translations

When you add a module or a string:

1. Add the key to **both** `lang/en/<module>.php` and `lang/ar/<module>.php`.
2. Enum labels → a nested group in the module file; keep values English.
3. New FormRequest → add `attributes()`.
4. Any user-facing JS string → `AppTranslations` or a `@json(__('module'))` inject.

Quick checks:

```bash
php artisan optimize:clear      # after touching lang files / settings
php artisan view:cache          # compiles every Blade — catches component/key typos

# en/ar key parity (flattened, incl. nested groups)
php -r 'function f($a,$p=""){$o=[];foreach($a as $k=>$v){$n=$p?"$p.$k":$k;
  $o=array_merge($o,is_array($v)?f($v,$n):[$n]);}return $o;}
  foreach(glob("lang/en/*.php") as $e){$b=basename($e);$a="lang/ar/$b";
  $d=array_merge(array_diff(f(require $e),f(require $a)),array_diff(f(require $a),f(require $e)));
  if($d)echo "$b: ".implode(",",$d)."\n";} echo "parity checked\n";'
```

> Default locale is **`en`** (existing default). To make new shops default to
> Arabic, change `regional.locale` in
> `App\Support\Settings\OrganizationSettings::DEFAULT_SETTINGS`.
