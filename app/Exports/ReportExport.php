<?php

declare(strict_types=1);

namespace App\Exports;

use App\Support\Reports\ReportDefinition;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Streams the report to .xlsx. Reuses the SAME definition base query, filters
 * and column map as the on-screen DataTable, so the export always matches the
 * filtered view the user is looking at.
 */
final class ReportExport implements FromQuery, WithHeadings, WithMapping
{
    /** @param array<string, mixed> $filters */
    public function __construct(
        private readonly ReportDefinition $definition,
        private readonly array $filters,
    ) {}

    public function query(): Builder
    {
        $request = new Request($this->filters);

        return $this->definition->applyFilters($this->definition->baseQuery(), $request);
    }

    /** @return array<int, string> */
    public function headings(): array
    {
        return array_values($this->definition->headings());
    }

    /**
     * @param  object  $row
     * @return array<int, int|string|null>
     */
    public function map($row): array
    {
        return array_values($this->definition->mapRow($row));
    }
}
