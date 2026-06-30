<?php

declare(strict_types=1);

namespace App\Support\DataTables;

use Closure;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Minimal server-side DataTables responder. Reusable by EVERY listing endpoint:
 *
 *   return DataTableBuilder::for($query, $request)
 *       ->searchable(['name', 'slug'])
 *       ->orderable(['id', 'name', 'created_at'])
 *       ->map(fn ($row) => ['id' => $row->id, 'name' => $row->name, ...])
 *       ->toArray();
 */
final class DataTableBuilder
{
    /** @var array<int, string> */
    private array $searchable = [];

    /** @var array<int, string> */
    private array $orderable = [];

    private ?Closure $map = null;

    private function __construct(
        private readonly Builder $query,
        private readonly Request $request,
    ) {}

    public static function for(Builder $query, Request $request): self
    {
        return new self($query, $request);
    }

    /** @param array<int, string> $columns */
    public function searchable(array $columns): self
    {
        $this->searchable = $columns;

        return $this;
    }

    /** @param array<int, string> $columns Whitelist of orderable column names. */
    public function orderable(array $columns): self
    {
        $this->orderable = $columns;

        return $this;
    }

    public function map(Closure $map): self
    {
        $this->map = $map;

        return $this;
    }

    /** @return array{draw:int,recordsTotal:int,recordsFiltered:int,data:array} */
    public function toArray(): array
    {
        $recordsTotal = (clone $this->query)->toBase()->getCountForPagination();

        $this->applySearch();

        $recordsFiltered = (clone $this->query)->toBase()->getCountForPagination();

        $this->applyOrder();

        $start = max(0, (int) $this->request->input('start', 0));
        $length = (int) $this->request->input('length', 10);
        if ($length > 0) {
            $this->query->offset($start)->limit($length);
        }

        $rows = $this->query->get();
        $data = $this->map !== null ? $rows->map($this->map)->all() : $rows->all();

        return [
            'draw' => (int) $this->request->input('draw', 1),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ];
    }

    private function applySearch(): void
    {
        $term = trim((string) $this->request->input('search.value', ''));
        if ($term === '' || $this->searchable === []) {
            return;
        }

        $this->query->where(function (Builder $q) use ($term): void {
            foreach ($this->searchable as $column) {
                $q->orWhere($column, 'like', "%{$term}%");
            }
        });
    }

    private function applyOrder(): void
    {
        $columnIndex = $this->request->input('order.0.column');
        $direction = $this->request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

        if ($columnIndex !== null) {
            $columnName = $this->request->input("columns.{$columnIndex}.data");
            if (is_string($columnName) && in_array($columnName, $this->orderable, true)) {
                $this->query->orderBy($columnName, $direction);

                return;
            }
        }

        // Stable default ordering.
        $this->query->orderByDesc($this->query->getModel()->getKeyName());
    }
}
