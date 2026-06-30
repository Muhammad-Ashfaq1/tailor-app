<?php

declare(strict_types=1);

namespace App\Support\Settings;

/**
 * Declarative schema for the per-section settings forms. Each field maps a form
 * input name => ['rules' => [...], 'path' => 'dot.path', 'cast' => 'bool|float|int'].
 * The 'path' is where the value lands in the settings JSON; the special
 * '@name' path writes to the organization's name column instead.
 *
 * Both SaveSettingsRequest (rules) and SettingController (persist) read this,
 * so adding a setting is a one-line change here.
 */
final class SettingsSchema
{
    public const SECTIONS = ['general', 'regional', 'operations', 'notifications'];

    /** @return array<string, array<string, array{rules:array,path:string,cast?:string}>> */
    public static function all(): array
    {
        return [
            'general' => [
                'name' => ['rules' => ['required', 'string', 'max:255'], 'path' => '@name'],
                'date_format' => ['rules' => ['required', 'string', 'max:20'], 'path' => 'regional.date_format'],
            ],
            'regional' => [
                'currency' => ['rules' => ['required', 'string', 'size:3'], 'path' => 'regional.currency'],
                'currency_symbol' => ['rules' => ['required', 'string', 'max:5'], 'path' => 'regional.currency_symbol'],
                'currency_position' => ['rules' => ['required', 'in:before,after'], 'path' => 'regional.currency_position'],
                'timezone' => ['rules' => ['required', 'timezone'], 'path' => 'regional.timezone'],
                'locale' => ['rules' => ['required', 'string', 'max:10'], 'path' => 'regional.locale'],
            ],
            'operations' => [
                'tax_enabled' => ['rules' => ['boolean'], 'path' => 'tax.enabled', 'cast' => 'bool'],
                'tax_rate' => ['rules' => ['numeric', 'min:0', 'max:100'], 'path' => 'tax.rate', 'cast' => 'float'],
                'tax_inclusive' => ['rules' => ['boolean'], 'path' => 'tax.inclusive', 'cast' => 'bool'],
                'invoice_prefix' => ['rules' => ['required', 'string', 'max:10'], 'path' => 'invoice.prefix'],
                'invoice_next_number' => ['rules' => ['required', 'integer', 'min:1'], 'path' => 'invoice.next_number', 'cast' => 'int'],
                'invoice_pad_length' => ['rules' => ['required', 'integer', 'min:1', 'max:12'], 'path' => 'invoice.pad_length', 'cast' => 'int'],
            ],
            'notifications' => [
                'email_enabled' => ['rules' => ['boolean'], 'path' => 'notifications.email_enabled', 'cast' => 'bool'],
                'notify_on_new_member' => ['rules' => ['boolean'], 'path' => 'notifications.notify_on_new_member', 'cast' => 'bool'],
                'notify_on_status_change' => ['rules' => ['boolean'], 'path' => 'notifications.notify_on_status_change', 'cast' => 'bool'],
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
}
