<?php

declare(strict_types=1);

namespace App\Reports;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Support\Reports\ReportDefinition;
use Illuminate\Contracts\Database\Eloquent\Builder;

/**
 * Tasks report over the org-scoped Task model.
 */
final class TasksReport extends ReportDefinition
{
    public function key(): string
    {
        return 'tasks';
    }

    public function label(): string
    {
        return 'Tasks';
    }

    public function baseQuery(): Builder
    {
        return Task::query()->with(['project', 'assignee']);
    }

    public function filters(): array
    {
        return [
            [
                'key' => 'status',
                'label' => 'Status',
                'type' => 'select',
                'options' => TaskStatus::options(),
            ],
        ];
    }

    public function sortableColumns(): array
    {
        return ['title', 'status', 'due_date', 'created_at'];
    }

    public function columnMap(): array
    {
        return [
            'title' => [
                'label' => 'Title',
                'value' => static fn (Task $t): string => $t->title,
            ],
            'project' => [
                'label' => 'Project',
                'value' => static fn (Task $t): ?string => $t->project?->name,
            ],
            'status' => [
                'label' => 'Status',
                'value' => static fn (Task $t): string => $t->status->label(),
            ],
            'assignee' => [
                'label' => 'Assignee',
                'value' => static fn (Task $t): ?string => $t->assignee?->name,
            ],
            'due_date' => [
                'label' => 'Due',
                'value' => static fn (Task $t): ?string => $t->due_date?->toDateString(),
            ],
            'created_at' => [
                'label' => 'Created',
                'value' => static fn (Task $t): ?string => $t->created_at?->toDateString(),
            ],
        ];
    }

    public function summary(Builder $query): array
    {
        $base = clone $query;

        return [
            ['label' => 'Total tasks', 'value' => (clone $base)->toBase()->getCountForPagination()],
            ['label' => 'In progress', 'value' => (clone $base)->where('status', TaskStatus::InProgress->value)->toBase()->getCountForPagination()],
            ['label' => 'Done', 'value' => (clone $base)->where('status', TaskStatus::Done->value)->toBase()->getCountForPagination()],
        ];
    }
}
