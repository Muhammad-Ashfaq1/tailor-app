<?php

declare(strict_types=1);

namespace App\Support\Settings;

use Illuminate\Support\Arr;

/**
 * The default per-organization settings tree. Stored values (the
 * organizations.settings JSON column) are deep-merged OVER these defaults on
 * read via Organization::mergedSettings(), so new default keys appear for
 * existing tenants without a migration.
 */
final class OrganizationSettings
{
    public const DEFAULT_SETTINGS = [
        'regional' => [
            'currency' => 'USD',
            'currency_symbol' => '$',
            'currency_position' => 'before', // before | after
            'timezone' => 'UTC',
            'locale' => 'en',
            'date_format' => 'Y-m-d',
        ],
        'tax' => [
            'enabled' => false,
            'rate' => 0.0,        // percent
            'inclusive' => false, // prices include tax
        ],
        'invoice' => [
            'prefix' => 'INV-',
            'next_number' => 1,
            'pad_length' => 5,
        ],
        'notifications' => [
            'email_enabled' => true,
            'notify_on_new_member' => true,
            'notify_on_status_change' => true,
        ],
        'business_hours' => [
            'monday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
            'tuesday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
            'wednesday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
            'thursday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
            'friday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
            'saturday' => ['open' => '09:00', 'close' => '13:00', 'closed' => false],
            'sunday' => ['open' => null, 'close' => null, 'closed' => true],
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
