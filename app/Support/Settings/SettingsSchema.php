<?php

declare(strict_types=1);

namespace App\Support\Settings;

/**
 * Declarative schema for the settings forms. Each field maps a form input name
 * => ['rules' => [...], 'path' => 'dot.path', 'cast' => 'bool|float|int']. The
 * 'path' is where the value lands in the settings JSON; the special '@name'
 * path writes to the organization's name column instead.
 *
 * Both SaveSettingsRequest (rules) and SettingController (persist) read this,
 * so adding a field is a one-line change here. Notifications is the one form
 * that isn't a flat scalar set (a per-event matrix), so it has its own request.
 */
final class SettingsSchema
{
    /** Route-based tabs. 'roles' just links out to the existing roles screen. */
    public const PAGES = ['profile', 'regional', 'operations', 'notifications', 'invoice', 'roles'];

    /** Scalar, schema-driven save sections (POST settings/save/{section}). */
    public const SECTIONS = ['profile', 'regional', 'operations', 'loyalty', 'invoice'];

    /** Events shown in the Notifications matrix (label per event key). */
    public const NOTIFICATION_EVENTS = [
        'order_placed' => 'Order placed',
        'order_ready' => 'Order ready',
        'order_delivered' => 'Order delivered',
        'payment_received' => 'Payment received',
        'measurement_updated' => 'Measurement updated',
    ];

    /** @return array<string, array<string, array{rules:array,path:string,cast?:string}>> */
    public static function all(): array
    {
        return [
            'profile' => [
                'shop_name' => ['rules' => ['required', 'string', 'max:255'], 'path' => '@name'],
                'business_name' => ['rules' => ['required', 'string', 'max:255'], 'path' => 'profile.business_name'],
                'owner_name' => ['rules' => ['required', 'string', 'max:255'], 'path' => 'profile.owner_name'],
                'website_url' => ['rules' => ['nullable', 'url', 'max:255'], 'path' => 'profile.website_url'],
                'business_email' => ['rules' => ['nullable', 'email', 'max:255'], 'path' => 'profile.business_email'],
                'business_phone' => ['rules' => ['nullable', 'string', 'max:30'], 'path' => 'profile.business_phone'],
                'address' => ['rules' => ['nullable', 'string', 'max:1000'], 'path' => 'profile.address'],
                'city' => ['rules' => ['nullable', 'string', 'max:120'], 'path' => 'profile.city'],
                'state' => ['rules' => ['nullable', 'string', 'max:120'], 'path' => 'profile.state'],
                'country' => ['rules' => ['nullable', 'string', 'max:120'], 'path' => 'profile.country'],
            ],
            'regional' => [
                'locale' => ['rules' => ['required', 'string', 'max:10'], 'path' => 'regional.locale'],
                'timezone' => ['rules' => ['required', 'timezone'], 'path' => 'regional.timezone'],
                'date_format' => ['rules' => ['required', 'string', 'max:20'], 'path' => 'regional.date_format'],
                'time_format' => ['rules' => ['required', 'string', 'max:20'], 'path' => 'regional.time_format'],
                'first_day_of_week' => ['rules' => ['required', 'in:saturday,sunday,monday'], 'path' => 'regional.first_day_of_week'],
                'currency' => ['rules' => ['required', 'string', 'size:3'], 'path' => 'regional.currency'],
                'currency_symbol' => ['rules' => ['required', 'string', 'max:5'], 'path' => 'regional.currency_symbol'],
                'currency_position' => ['rules' => ['required', 'in:before,after'], 'path' => 'regional.currency_position'],
                'currency_decimals' => ['rules' => ['required', 'integer', 'min:0', 'max:4'], 'path' => 'regional.currency_decimals', 'cast' => 'int'],
            ],
            'operations' => [
                'default_stitching_type' => ['rules' => ['nullable', 'string', 'max:100'], 'path' => 'operations.default_stitching_type'],
                'measurement_unit' => ['rules' => ['required', 'in:cm,inch'], 'path' => 'operations.measurement_unit'],
                'default_delivery_type' => ['rules' => ['required', 'in:shop_pickup,home_delivery'], 'path' => 'operations.default_delivery_type'],
                'home_delivery_charge' => ['rules' => ['required', 'numeric', 'min:0'], 'path' => 'operations.home_delivery_charge', 'cast' => 'float'],
            ],
            'loyalty' => [
                'default_credit_type' => ['rules' => ['required', 'in:none,percentage,fixed'], 'path' => 'loyalty.default_credit_type'],
                'default_credit_value' => ['rules' => ['required', 'numeric', 'min:0'], 'path' => 'loyalty.default_credit_value', 'cast' => 'float'],
            ],
            'invoice' => [
                'prefix' => ['rules' => ['required', 'string', 'max:10'], 'path' => 'invoice.prefix'],
                'next_number' => ['rules' => ['required', 'integer', 'min:1'], 'path' => 'invoice.next_number', 'cast' => 'int'],
                'pad_length' => ['rules' => ['required', 'integer', 'min:1', 'max:12'], 'path' => 'invoice.pad_length', 'cast' => 'int'],
                'payment_terms_days' => ['rules' => ['required', 'integer', 'min:0', 'max:365'], 'path' => 'invoice.payment_terms_days', 'cast' => 'int'],
                'tax_rate' => ['rules' => ['required', 'numeric', 'min:0', 'max:100'], 'path' => 'invoice.tax_rate', 'cast' => 'float'],
                'footer_notes' => ['rules' => ['nullable', 'string', 'max:1000'], 'path' => 'invoice.footer_notes'],
            ],
        ];
    }

    /** @return array<string, array{rules:array,path:string,cast?:string}> */
    public static function section(string $section): array
    {
        return self::all()[$section] ?? [];
    }

    public static function isValidSection(string $section): bool
    {
        return in_array($section, self::SECTIONS, true);
    }

    public static function isValidPage(string $page): bool
    {
        return in_array($page, self::PAGES, true);
    }
}
