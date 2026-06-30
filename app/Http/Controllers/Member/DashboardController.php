<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

/**
 * The member's personal dashboard: simple counts of the tasks assigned to the
 * authenticated user, broken down by status. Org isolation is automatic via the
 * Task global scope; the assignee filter narrows it to "mine".
 */
final readonly class DashboardController extends Controller
{
    public function index(): View
    {
        $userId = auth()->id();

        $counts = Task::query()
            ->where('assigned_to', $userId)
            ->select('status', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $byStatus = array_map(
            static fn (TaskStatus $status): array => [
                'value' => $status->value,
                'label' => $status->label(),
                'color' => $status->color(),
                'count' => (int) ($counts[$status->value] ?? 0),
            ],
            TaskStatus::cases(),
        );

        return view('member.dashboard', [
            'totalTasks' => (int) $counts->sum(),
            'byStatus' => $byStatus,
        ]);
    }
}
