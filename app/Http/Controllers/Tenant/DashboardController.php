<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\TenantDashboardService;
use Illuminate\Contracts\View\View;

/**
 * Tenant analytics landing. The service builds a fully org-scoped payload that
 * the Blade view hands straight to ApexCharts via @json.
 */
final readonly class DashboardController extends Controller
{
    public function __construct(
        private TenantDashboardService $dashboard,
    ) {}

    public function index(): View
    {
        return view('tenant.dashboard', [
            'payload' => $this->dashboard->build(),
        ]);
    }
}
