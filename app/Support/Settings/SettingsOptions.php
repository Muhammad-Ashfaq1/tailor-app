<?php

declare(strict_types=1);

namespace App\Support\Settings;

/**
 * Dropdown option sets for the settings forms. Kept in one place so the same
 * lists back both the Blade selects and (where useful) validation.
 */
final class SettingsOptions
{
    /** @return array<string, string> value => label */
    public static function locales(): array
    {
        return [
            'en' => 'English',
            'ar' => 'العربية (Arabic)',
        ];
    }

    /** A curated timezone list (Gulf-first) — the rule 'timezone' still validates any valid id. */
    public static function timezones(): array
    {
        return [
            'Asia/Riyadh' => '(GMT+03:00) Riyadh',
            'Asia/Dubai' => '(GMT+04:00) Dubai',
            'Asia/Qatar' => '(GMT+03:00) Qatar',
            'Asia/Kuwait' => '(GMT+03:00) Kuwait',
            'Asia/Bahrain' => '(GMT+03:00) Bahrain',
            'Asia/Karachi' => '(GMT+05:00) Karachi',
            'Africa/Cairo' => '(GMT+02:00) Cairo',
            'Europe/London' => '(GMT+00:00) London',
            'America/New_York' => '(GMT-05:00) New York',
            'UTC' => '(GMT+00:00) UTC',
        ];
    }

    /** value => live-formatted example (uses a fixed sample date). */
    public static function dateFormats(): array
    {
        $sample = mktime(13, 30, 0, 1, 31, 2025); // 31 Jan 2025, 13:30

        return collect(['d M Y', 'M d, Y', 'Y-m-d', 'd/m/Y', 'm/d/Y'])
            ->mapWithKeys(fn (string $f): array => [$f => date($f, $sample)])
            ->all();
    }

    /** @return array<string, string> */
    public static function timeFormats(): array
    {
        $sample = mktime(13, 30, 0, 1, 31, 2025);

        return [
            'h:i A' => '12 Hour ('.date('h:i A', $sample).')',
            'H:i' => '24 Hour ('.date('H:i', $sample).')',
        ];
    }

    /** @return array<string, string> */
    public static function firstDaysOfWeek(): array
    {
        return [
            'saturday' => 'Saturday',
            'sunday' => 'Sunday',
            'monday' => 'Monday',
        ];
    }

    /** @return array<string, string> */
    public static function currencyPositions(): array
    {
        return [
            'before' => 'Before (SAR 100)',
            'after' => 'After (100 SAR)',
        ];
    }

    /** @return array<string, string> */
    public static function measurementUnits(): array
    {
        return [
            'cm' => 'Centimetres (cm)',
            'inch' => 'Inches (in)',
        ];
    }

    /** @return array<string, string> */
    public static function deliveryTypes(): array
    {
        return [
            'shop_pickup' => 'Shop pickup',
            'home_delivery' => 'Home delivery',
        ];
    }
}
