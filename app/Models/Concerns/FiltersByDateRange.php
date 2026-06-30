<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Reusable date-range filtering for listings & the report engine.
 * Usage: Model::query()->dateRange('created_at', $from, $to)
 */
trait FiltersByDateRange
{
    public function scopeDateRange(Builder $query, string $column, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn (Builder $q) => $q->whereDate($column, '>=', $from))
            ->when($to, fn (Builder $q) => $q->whereDate($column, '<=', $to));
    }
}
