<?php

declare(strict_types=1);

namespace App\Support\Settings;

use Illuminate\Support\Arr;

/**
 * The default per-organization settings tree. Stored values (the
 * organizations.settings JSON column) are deep-merged OVER these defaults on
 * read via Organization::mergedSettings(), so new default keys appear for
 * existing tenants without a migration. This JSON column is the single flexible
 * store for the whole settings area.
 */
final class OrganizationSettings
{
    public const DEFAULT_SETTINGS = [
        'profile' => [
            'business_name' => null,
            'owner_name' => null,
            'website_url' => null,
            'business_email' => null,
            'business_phone' => null,
            'address' => null,
            'city' => null,
            'state' => null,
            'country' => null,
        ],
        'regional' => [
            // Currency (read by App\Support\Currency + the @money directive).
            'currency' => 'SAR',
            'currency_symbol' => 'SAR',
            'currency_position' => 'after',  // before | after
            'currency_decimals' => 2,
            'thousands_separator' => ',',
            'decimal_separator' => '.',
            // Locale + regional formatting.
            'locale' => 'en',           // en | ar (visible UI language)
            'direction' => 'ltr',       // ltr | rtl — always derived from locale at runtime
            'country' => 'SA',
            'timezone' => 'Asia/Riyadh',
            'date_format' => 'd M Y',
            'time_format' => 'h:i A',
            'first_day_of_week' => 'sunday',
        ],
        'operations' => [
            'default_stitching_type' => null,
            'measurement_unit' => 'cm',               // cm | inch
            'default_delivery_type' => 'shop_pickup', // shop_pickup | home_delivery
            'home_delivery_charge' => 0.0,
        ],
        'loyalty' => [
            'default_credit_type' => 'none',  // none | percentage | fixed
            'default_credit_value' => 0.0,
        ],
        'invoice' => [
            'prefix' => 'INV-',
            'next_number' => 1,
            'pad_length' => 5,
            'payment_terms_days' => 0,
            'tax_rate' => 0.0,        // percent
            'footer_notes' => null,
        ],
        'notifications' => [
            // Keyed event => per-channel toggles. Add a new event here (and in
            // SettingsSchema::NOTIFICATION_EVENTS) with no migration.
            'events' => [
                'order_placed' => ['email' => true, 'in_app' => true],
                'order_ready' => ['email' => true, 'in_app' => true],
                'order_delivered' => ['email' => false, 'in_app' => true],
                'payment_received' => ['email' => true, 'in_app' => true],
                'measurement_updated' => ['email' => false, 'in_app' => true],
            ],
        ],
    ];

    /** Deep-merge stored settings over the defaults. */
    public static function merged(array $stored): array
    {
        return self::deepMerge(self::DEFAULT_SETTINGS, $stored);
    }

    public static function defaults(): array
    {
        return self::DEFAULT_SETTINGS;
    }

    /**
     * Recursive array merge where associative children merge and scalar/list
     * values from $override win.
     */
    private static function deepMerge(array $base, array $override): array
    {
        foreach ($override as $key => $value) {
            if (
                is_array($value)
                && isset($base[$key])
                && is_array($base[$key])
                && Arr::isAssoc($base[$key])
            ) {
                $base[$key] = self::deepMerge($base[$key], $value);
            } else {
                $base[$key] = $value;
            }
        }

        return $base;
    }
}
