<?php

declare(strict_types=1);

namespace App\Support\Reports;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Contract + shared behaviour for a single report. A concrete report only has
 * to describe its data: the base (org-scoped) query, which column carries the
 * date for range filtering, the allowed filters, the orderable columns, the
 * column map (label + value extractor) and the summary aggregates.
 *
 * Adding a new report = subclass this + add one line to ReportRegistry::$map.
 */
abstract class ReportDefinition
{
    /** Stable key used in the URL (e.g. "projects"). */
    abstract public function key(): string;

    /** Human label shown in the UI. */
    abstract public function label(): string;

    /** The org-scoped Eloquent query the report runs over. */
    abstract public function baseQuery(): Builder;

    /**
     * key => ['label' => string, 'value' => fn (Model $row): scalar]
     *
     * @return array<string, array{label: string, value: callable}>
     */
    abstract public function columnMap(): array;

    /** The column used for date-range filtering. Null disables it. */
    public function dateColumn(): ?string
    {
        return 'created_at';
    }

    /**
     * Declarative filter definitions for the view (e.g. status select options).
     *
     * @return array<int, array<string, mixed>>
     */
    public function filters(): array
    {
        return [];
    }

    /**
     * Whitelist of column keys the DataTable may order by.
     *
     * @return array<int, string>
     */
    public function sortableColumns(): array
    {
        return array_keys($this->columnMap());
    }

    /**
     * Aggregate stats shown above the table. Receives the already-filtered query.
     *
     * @return array<int, array{label: string, value: int|string}>
     */
    public function summary(Builder $query): array
    {
        return [
            ['label' => 'Total', 'value' => (clone $query)->toBase()->getCountForPagination()],
        ];
    }

    /**
     * Apply the shared filters (date range + a "status" equality filter when the
     * report exposes one) to a query. Reused by the listing, summary and export
     * so every surface stays consistent.
     */
    public function applyFilters(Builder $query, Request $request): Builder
    {
        $column = $this->dateColumn();

        if ($column !== null) {
            $from = $this->normalizeDate($request->input('date_from'));
            $to = $this->normalizeDate($request->input('date_to'));

            $query
                ->when($from, fn (Builder $q) => $q->whereDate($column, '>=', $from))
                ->when($to, fn (Builder $q) => $q->whereDate($column, '<=', $to));
        }

        $status = $request->input('status');
        if (is_string($status) && $status !== '' && $this->hasStatusColumn()) {
            $query->where('status', $status);
        }

        return $query;
    }

    /** Column labels keyed by column key, for headings (table + export). */
    public function headings(): array
    {
        return array_map(
            static fn (array $column): string => $column['label'],
            $this->columnMap(),
        );
    }

    /**
     * Map a single model row to an ordered associative array using columnMap().
     *
     * @return array<string, int|string|null>
     */
    public function mapRow(object $row): array
    {
        $mapped = [];
        foreach ($this->columnMap() as $key => $column) {
            $mapped[$key] = ($column['value'])($row);
        }

        return $mapped;
    }

    private function hasStatusColumn(): bool
    {
        return collect($this->filters())
            ->contains(fn (array $filter): bool => ($filter['key'] ?? null) === 'status');
    }

    private function normalizeDate(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }
}
