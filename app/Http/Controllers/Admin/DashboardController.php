<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\OrganizationStatus;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

/**
 * Super-admin platform overview. Organization is the tenant model (no org
 * global scope) so its plain query spans all tenants. Deliberately free of any
 * Lead dependency — that surface is owned elsewhere.
 */
final readonly class DashboardController extends Controller
{
    public function index(): View
    {
        $orgCounts = Organization::query()
            ->select('status', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $orgsByStatus = array_map(
            static fn (OrganizationStatus $status): array => [
                'value' => $status->value,
                'label' => $status->label(),
                'color' => $status->color(),
                'count' => (int) ($orgCounts[$status->value] ?? 0),
            ],
            OrganizationStatus::cases(),
        );

        return view('admin.dashboard', [
            'totalOrganizations' => (int) $orgCounts->sum(),
            'totalUsers' => User::query()->count(),
            'orgsByStatus' => $orgsByStatus,
        ]);
    }
}
