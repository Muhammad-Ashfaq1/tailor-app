<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exports\ReportExport;
use App\Repositories\ReportsRepository;
use App\Support\Reports\ReportRegistry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Surface-agnostic report controller. The layout and the child route-name
 * prefix are derived from the current route name (tenant.* / admin.* / member.*)
 * so a single controller serves every panel without duplication.
 */
final readonly class ReportController extends Controller
{
    public function __construct(
        private ReportsRepository $reports,
    ) {}

    public function index(Request $request): View
    {
        return view('reports.index', [
            'reports' => ReportRegistry::all(),
            'layout' => $this->layout($request),
            'prefix' => $this->prefix($request),
        ]);
    }

    public function show(Request $request, string $report): View
    {
        return view('reports.show', [
            'definition' => ReportRegistry::get($report),
            'layout' => $this->layout($request),
            'prefix' => $this->prefix($request),
        ]);
    }

    public function listing(Request $request, string $report): JsonResponse
    {
        $definition = ReportRegistry::get($report);

        return response()->json($this->reports->datatable($definition, $request));
    }

    public function export(Request $request, string $report): BinaryFileResponse
    {
        $definition = ReportRegistry::get($report);

        return Excel::download(
            new ReportExport($definition, $request->all()),
            "{$report}.xlsx",
        );
    }

    /** tenant.* + admin.* render the full app shell; member.* the focused portal. */
    private function layout(Request $request): string
    {
        return $this->prefix($request) === 'member'
            ? 'layouts.member-portal'
            : 'layouts.app';
    }

    /** The first route-name segment, e.g. "tenant.reports.show" -> "tenant". */
    private function prefix(Request $request): string
    {
        $name = (string) ($request->route()?->getName() ?? '');

        return explode('.', $name)[0] ?: 'tenant';
    }
}
