<?php

declare(strict_types=1);

namespace App\Support\Reports;

/**
 * Static catalogue of available reports. Adding a report is a one-line change:
 * register its key => DefinitionClass here. No service provider required.
 */
final class ReportRegistry
{
    /** @var array<string, class-string<ReportDefinition>> */
    private static array $map = [
        // Register reports here, e.g. 'invoices' => InvoicesReport::class
    ];

    /**
     * All reports, instantiated, keyed by their key.
     *
     * @return array<string, ReportDefinition>
     */
    public static function all(): array
    {
        $reports = [];
        foreach (self::$map as $key => $class) {
            $reports[$key] = new $class;
        }

        return $reports;
    }

    /** Resolve a single report by key, or 404 when unknown. */
    public static function get(string $key): ReportDefinition
    {
        $class = self::$map[$key] ?? null;

        if ($class === null) {
            abort(404, "Unknown report [{$key}].");
        }

        return new $class;
    }
}
