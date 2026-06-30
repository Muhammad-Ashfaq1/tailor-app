<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Organization;
use App\Support\Settings\OrganizationSettings;
use App\Support\Tenancy\OrganizationContext;

/**
 * Per-organization currency formatting. Reads the current org's regional
 * settings (falling back to defaults for central / super-admin context).
 * Backs the @money() Blade directive and window.appCurrency for JS.
 */
final class Currency
{
    private static ?array $cache = null;

    /** @return array{currency:string,symbol:string,position:string,decimals:int} */
    public static function config(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $regional = OrganizationSettings::DEFAULT_SETTINGS['regional'];

        $orgId = OrganizationContext::id();
        if ($orgId !== null) {
            $org = Organization::withoutGlobalScopes()->find($orgId);
            if ($org !== null) {
                $regional = $org->mergedSettings()['regional'];
            }
        }

        return self::$cache = [
            'currency' => $regional['currency'] ?? 'USD',
            'symbol' => $regional['currency_symbol'] ?? '$',
            'position' => $regional['currency_position'] ?? 'before',
            'decimals' => 2,
        ];
    }

    public static function format(float|int|string|null $amount): string
    {
        $c = self::config();
        $value = number_format((float) $amount, $c['decimals']);

        return $c['position'] === 'after'
            ? $value.$c['symbol']
            : $c['symbol'].$value;
    }

    /** JSON-safe config exposed to the browser as window.appCurrency. */
    public static function jsConfig(): array
    {
        return self::config();
    }

    /** Drop the memoised config (e.g. after switching tenant in a worker). */
    public static function flush(): void
    {
        self::$cache = null;
    }
}
