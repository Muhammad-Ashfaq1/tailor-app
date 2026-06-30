<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Support\Tenancy\OrganizationContext;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Builds the tenant dashboard payload. Everything is org-scoped: members are
 * filtered by the current organization context. The returned array is a plain,
 * JSON-safe structure consumed by tenant.dashboard + ApexCharts.
 */
final readonly class TenantDashboardService
{
    private const TREND_DAYS = 14;

    /**
     * @return array{
     *     stats: array<string, int>,
     *     members_by_role: array<int, array{role:string,count:int}>,
     *     trend: array{labels: array<int, string>, data: array<int, int>}
     * }
     */
    public function build(): array
    {
        return [
            'stats' => $this->stats(),
            'members_by_role' => $this->membersByRole(),
            'trend' => $this->memberTrend(),
        ];
    }

    /** @return array<string, int> */
    private function stats(): array
    {
        return [
            'total_members' => $this->members()->count(),
            'active_members' => $this->members()->where('is_active', true)->count(),
        ];
    }

    private function members(): \Illuminate\Database\Eloquent\Builder
    {
        $orgId = OrganizationContext::id();

        return User::query()
            ->when($orgId !== null, fn ($q) => $q->where('organization_id', $orgId));
    }

    /** @return array<int, array{role:string,count:int}> */
    private function membersByRole(): array
    {
        return $this->members()
            ->select('role', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('role')
            ->pluck('aggregate', 'role')
            ->map(static fn ($count, $role): array => ['role' => (string) $role, 'count' => (int) $count])
            ->values()
            ->all();
    }

    /**
     * Members joined per day for the last TREND_DAYS days (inclusive of today).
     *
     * @return array{labels: array<int, string>, data: array<int, int>}
     */
    private function memberTrend(): array
    {
        $to = CarbonImmutable::now();
        $from = $to->subDays(self::TREND_DAYS - 1)->startOfDay();

        $counts = $this->members()
            ->whereBetween('created_at', [$from, $to])
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('COUNT(*) as aggregate'))
            ->groupBy('day')
            ->pluck('aggregate', 'day');

        $labels = [];
        $data = [];

        for ($i = 0; $i < self::TREND_DAYS; $i++) {
            $day = $from->addDays($i);
            $labels[] = $day->format('M j');
            $data[] = (int) ($counts[$day->toDateString()] ?? 0);
        }

        return ['labels' => $labels, 'data' => $data];
    }
}
