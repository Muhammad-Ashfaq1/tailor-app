<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Support\DataTables\DataTableBuilder;
use App\Support\Reports\ReportDefinition;
use Illuminate\Http\Request;

/**
 * Drives a report's DataTable: it applies the definition's filters to its base
 * query, then maps each row through the definition's columnMap via the shared
 * DataTableBuilder — the same builder every other listing endpoint uses.
 */
final class ReportsRepository
{
    /** @return array{draw:int,recordsTotal:int,recordsFiltered:int,data:array} */
    public function datatable(ReportDefinition $definition, Request $request): array
    {
        $query = $definition->applyFilters($definition->baseQuery(), $request);

        return DataTableBuilder::for($query, $request)
            ->searchable($this->searchable($definition))
            ->orderable($definition->sortableColumns())
            ->map(fn (object $row): array => $definition->mapRow($row))
            ->toArray();
    }

    /**
     * Only real DB columns from the definition's sortable set are safe to LIKE
     * against; computed/relation columns are excluded from free-text search.
     *
     * @return array<int, string>
     */
    private function searchable(ReportDefinition $definition): array
    {
        return array_values(array_intersect(
            $definition->sortableColumns(),
            array_keys($definition->columnMap()),
        ));
    }
}
