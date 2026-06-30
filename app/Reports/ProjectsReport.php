<?php

declare(strict_types=1);

namespace App\Reports;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Support\Reports\ReportDefinition;
use Illuminate\Contracts\Database\Eloquent\Builder;

/**
 * Projects report over the org-scoped Project model.
 */
final class ProjectsReport extends ReportDefinition
{
    public function key(): string
    {
        return 'projects';
    }

    public function label(): string
    {
        return 'Projects';
    }

    public function baseQuery(): Builder
    {
        return Project::query()->withCount('tasks');
    }

    public function filters(): array
    {
        return [
            [
                'key' => 'status',
                'label' => 'Status',
                'type' => 'select',
                'options' => ProjectStatus::options(),
            ],
        ];
    }

    public function sortableColumns(): array
    {
        return ['name', 'slug', 'status', 'created_at'];
    }

    public function columnMap(): array
    {
        return [
            'name' => [
                'label' => 'Name',
                'value' => static fn (Project $p): string => $p->name,
            ],
            'slug' => [
                'label' => 'Slug',
                'value' => static fn (Project $p): string => $p->slug,
            ],
            'status' => [
                'label' => 'Status',
                'value' => static fn (Project $p): string => $p->status->label(),
            ],
            'tasks_count' => [
                'label' => 'Tasks',
                'value' => static fn (Project $p): int => (int) ($p->tasks_count ?? 0),
            ],
            'created_at' => [
                'label' => 'Created',
                'value' => static fn (Project $p): ?string => $p->created_at?->toDateString(),
            ],
        ];
    }

    public function summary(Builder $query): array
    {
        $base = clone $query;

        return [
            ['label' => 'Total projects', 'value' => (clone $base)->toBase()->getCountForPagination()],
            ['label' => 'Active', 'value' => (clone $base)->where('status', ProjectStatus::Active->value)->toBase()->getCountForPagination()],
            ['label' => 'Completed', 'value' => (clone $base)->where('status', ProjectStatus::Completed->value)->toBase()->getCountForPagination()],
        ];
    }
}
