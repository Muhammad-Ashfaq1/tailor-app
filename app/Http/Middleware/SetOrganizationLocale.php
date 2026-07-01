<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * set.organization.locale — resolve the visible UI language from the current
 * organization's regional settings and apply it for the request.
 *
 * Runs AFTER org.init so tenancy is already initialised; it only reads the
 * current tenant (an Organization instance) — it never fires an org-scoped
 * query. Central / super-admin context (no tenant) falls back to the app
 * default locale, so the admin panel stays English unless configured otherwise.
 *
 * Direction is always derived from the locale (ar => rtl, otherwise ltr) so the
 * two can never drift out of sync. Both are stashed in the session for Blade /
 * JS (see the layout <html dir> and window.AppDirection).
 */
final class SetOrganizationLocale
{
    /** @var list<string> */
    private const SUPPORTED = ['en', 'ar'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale();

        if (! in_array($locale, self::SUPPORTED, true)) {
            $locale = 'en';
        }

        $direction = $locale === 'ar' ? 'rtl' : 'ltr';

        App::setLocale($locale);

        $request->session()->put([
            'app_locale' => $locale,
            'app_direction' => $direction,
        ]);

        return $next($request);
    }

    /**
     * Read regional.locale from the current tenant only. No org-scoped query is
     * issued: tenant() returns the Organization already resolved by org.init.
     */
    private function resolveLocale(): string
    {
        $default = (string) config('app.locale', 'en');

        $tenant = tenant();
        if ($tenant instanceof Organization) {
            return (string) data_get($tenant->mergedSettings(), 'regional.locale', $default);
        }

        return $default;
    }
}
