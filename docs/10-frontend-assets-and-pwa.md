# 10 — Frontend, Assets & PWA

How the UI is delivered: server-rendered Blade + Bootstrap/Vuexy with **static
assets** (no bundler in the request path), plus a tenant-safe PWA service worker.

See also: [CRUD module convention](06-crud-module-convention.md) ·
[Settings](05-settings.md) · [Public landing](08-public-landing-and-leads.md)

---

## Overview

The admin UI is the **Vuexy** Bootstrap 5 template. Critically, **the runtime
loads pre-built static files** copied to `public/organization/` and referenced
via `asset()`. There is no Vite/webpack step required to run the app — pages
load plain `<script>`/`<link>` tags. jQuery powers DataTables and Select2;
SweetAlert2 handles confirmations; axios does AJAX.

> A Vite config and `resources/js|css` exist for Tailwind-on-the-landing-side
> tooling, but the **admin panels do not depend on a build** — they consume the
> static `public/organization/*` assets directly. See the gotcha below.

---

## Key files

| File | Responsibility |
| --- | --- |
| `resources/views/layouts/app.blade.php` | Main admin shell (sidebar/navbar/content). |
| `resources/views/layouts/member-portal.blade.php` | Member panel shell. |
| `resources/views/layouts/partials/head.blade.php` | CSS + axios + `app.js` + `window.appCurrency`. |
| `resources/views/layouts/partials/scripts.blade.php` | jQuery/Bootstrap core + `@stack` hooks. |
| `resources/views/layouts/partials/pwa-head.blade.php` | Manifest + service-worker registration. |
| `public/organization/**` | Static Vuexy theme + libs (jQuery, DataTables, Select2, SweetAlert2, ApexCharts, axios). |
| `public/organization/js/app.js` | CSRF-aware axios defaults + `window.formatMoney`. |
| `public/sw.js` | Service worker (tenant-safe caching). |
| `public/manifest.webmanifest` | PWA manifest. |
| `resources/views/vuexy-template/` | Upstream template **source**, kept for reference only. |

---

## How it works

### 1. Static assets, no build step

The layout head loads everything as plain static files under
`public/organization/` via `asset()`:

```blade
{{-- resources/views/layouts/partials/head.blade.php --}}
<link rel="stylesheet" href="{{ asset('organization/vendor/css/core.css') }}">
<script>window.appCurrency = @json(\App\Support\Currency::jsConfig());</script>
<script src="{{ asset('organization/libs/axios/axios.min.js') }}"></script>
<script src="{{ asset('organization/js/app.js') }}"></script>
```

```blade
{{-- resources/views/layouts/partials/scripts.blade.php --}}
<script src="{{ asset('organization/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('organization/vendor/js/bootstrap.js') }}"></script>
@stack('vendor-scripts')
<script src="{{ asset('organization/js/main.js') }}"></script>
@stack('scripts')
```

Per-page libraries are added with `@push('vendor-styles')` /
`@push('vendor-scripts')` (e.g. DataTables + Select2 + SweetAlert2 on the
Projects page), and page logic with `@push('scripts')` — see the canonical
listing pattern in [CRUD convention](06-crud-module-convention.md).

`data-assets-path="{{ asset('organization/') }}/"` on `<html>` tells the Vuexy
theme JS where to find its assets.

### 2. The app.js contract

`public/organization/js/app.js` is loaded after axios and owns two
cross-cutting concerns:

```js
// CSRF-aware axios defaults (reads <meta name="csrf-token">)
window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;

// Client mirror of App\Support\Currency::format(), driven by window.appCurrency
window.formatMoney = function (amount) {
    var c = window.appCurrency || { symbol: '$', position: 'before', decimals: 2 };
    var value = Number(amount || 0).toFixed(c.decimals == null ? 2 : c.decimals);
    return c.position === 'after' ? value + c.symbol : c.symbol + value;
};
```

So `@money()` (server, see [settings](05-settings.md)) and `formatMoney()`
(client) format currency identically per organization.

### 3. PWA — manifest + tenant-safe service worker

The manifest and SW registration are injected into every layout head:

```blade
{{-- resources/views/layouts/partials/pwa-head.blade.php --}}
<link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
<meta name="theme-color" content="#7367f0">
<script>
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
      navigator.serviceWorker.register('{{ asset('sw.js') }}').catch(console.warn);
    });
  }
</script>
```

The service worker uses a **split strategy that prevents cross-tenant
leakage** — only immutable static assets are cached; all server-rendered,
org-scoped HTML is network-first and never cached:

```js
// public/sw.js
function isStaticAsset(url) {
    return url.pathname.startsWith('/organization/') || url.pathname.startsWith('/build/');
}
self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;
    const url = new URL(event.request.url);
    if (isStaticAsset(url)) {
        // CACHE-FIRST for immutable static assets only.
        event.respondWith(/* cache.match || fetch+cache */);
        return;
    }
    // NETWORK-FIRST, NO caching, for everything else (org-scoped HTML/JSON).
    event.respondWith(fetch(event.request).catch(() => caches.match(event.request)));
});
```

This is the front-end half of the isolation model: one tenant can never be
served another tenant's HTML from cache.

### 4. The `vuexy-template/` source

`resources/views/vuexy-template/` is the **upstream Vuexy template source**
(SCSS, full lib set, build config) kept purely for reference. The running app
uses only the extracted static assets in `public/organization/`.

---

## How to extend

- **Add a page-specific library:** drop the static file under
  `public/organization/vendor/libs/<lib>/` and reference it via
  `@push('vendor-styles')` / `@push('vendor-scripts')` in that page's Blade.
- **Add a global JS behaviour:** extend `public/organization/js/app.js` (it's
  loaded everywhere).
- **Change PWA branding:** edit `public/manifest.webmanifest` (and the icons it
  points at) and the `theme-color` in `pwa-head.blade.php`.

---

## Gotchas

- **Do not assume a build step at runtime.** The panels load static
  `public/organization/*` files via `asset()`; you add libraries by copying
  files, not by importing into a bundle. (Vite/Tailwind tooling exists for the
  landing side but the admin UI doesn't require `npm run build` to function.)
- **Never cache HTML in the service worker.** Only `/organization/` and `/build/`
  are cache-first; caching org-scoped HTML would risk serving one tenant another
  tenant's page.
- The CSRF token comes from `<meta name="csrf-token">`; keep that meta tag in
  every layout head or axios POSTs will 419.
- `window.appCurrency` is injected per request from the current org's settings —
  client `formatMoney()` only matches the server when that injection is present.
- Bump the SW cache name (`tailor-static-v1`) when you ship new static assets, or
  clients may serve stale cached files.
