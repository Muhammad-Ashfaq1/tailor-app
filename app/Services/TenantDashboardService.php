<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Support\Tenancy\OrganizationContext;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Builds the tenant dashboard payload. Everything is org-scoped automatically:
 * Project & Task carry the BelongsToOrganization global scope, and active
 * members are filtered by the current organization context. The returned array
 * is a plain, JSON-safe structure consumed by tenant.dashboard + ApexCharts.
 */
final readonly class TenantDashboardService
{
    private const TREND_DAYS = 14;

    /**
     * @return array{
     *     stats: array<string, int>,
     *     tasks_by_status: array<int, array{value:string,label:string,color:string,count:int}>,
     *     projects_by_status: array<int, array{value:string,label:string,color:string,count:int}>,
     *     trend: array{labels: array<int, string>, data: array<int, int>}
     * }
     */
    public function build(): array
    {
        return [
            'stats' => $this->stats(),
            'tasks_by_status' => $this->tasksByStatus(),
            'projects_by_status' => $this->projectsByStatus(),
            'trend' => $this->taskTrend(),
        ];
    }

    /** @return array<string, int> */
    private function stats(): array
    {
        return [
            'total_projects' => Project::query()->count(),
            'total_tasks' => Task::query()->count(),
            'active_members' => $this->activeMembers(),
            'open_tasks' => Task::query()->where('status', '!=', TaskStatus::Done->value)->count(),
        ];
    }

    private function activeMembers(): int
    {
        $orgId = OrganizationContext::id();

        return User::query()
            ->when($orgId !== null, fn ($q) => $q->where('organization_id', $orgId))
            ->count();
    }

    /** @return array<int, array{value:string,label:string,color:string,count:int}> */
    private function tasksByStatus(): array
    {
        $counts = Task::query()
            ->select('status', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return array_map(
            static fn (TaskStatus $status): array => [
                'value' => $status->value,
                'label' => $status->label(),
                'color' => $status->color(),
                'count' => (int) ($counts[$status->value] ?? 0),
            ],
            TaskStatus::cases(),
        );
    }

    /** @return array<int, array{value:string,label:string,color:string,count:int}> */
    private function projectsByStatus(): array
    {
        $counts = Project::query()
            ->select('status', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return array_map(
            static fn (ProjectStatus $status): array => [
                'value' => $status->value,
                'label' => $status->label(),
                'color' => $status->color(),
                'count' => (int) ($counts[$status->value] ?? 0),
            ],
            ProjectStatus::cases(),
        );
    }

    /**
     * Tasks created per day for the last TREND_DAYS days (inclusive of today).
     *
     * @return array{labels: array<int, string>, data: array<int, int>}
     */
    private function taskTrend(): array
    {
        $to = CarbonImmutable::now();
        $from = $to->subDays(self::TREND_DAYS - 1)->startOfDay();

        $counts = Task::query()
            ->dateRange('created_at', $from->toDateString(), $to->toDateString())
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('COUNT(*) as aggregate'))
            ->groupBy('day')
            ->pluck('aggregate', 'day');

        $labels = [];
        $data = [];

        for ($i = 0; $i < self::TREND_DAYS; $i++) {
            $day = $from->addDays($i);
            $key = $day->toDateString();
            $labels[] = $day->format('M j');
            $data[] = (int) ($counts[$key] ?? 0);
        }

        return ['labels' => $labels, 'data' => $data];
    }
}
