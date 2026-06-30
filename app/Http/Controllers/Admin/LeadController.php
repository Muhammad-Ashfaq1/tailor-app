<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Repositories\Interface\LeadRepositoryInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Super-admin lead triage. Leads are central, so no org scope applies; the
 * route group already enforces the super_admin gate.
 */
final readonly class LeadController extends Controller
{
    public function __construct(
        private LeadRepositoryInterface $leads,
    ) {}

    /** Page chrome — the DataTable hydrates itself from listing(). */
    public function index(): View
    {
        return view('admin.leads.index', [
            'statuses' => LeadStatus::options(),
        ]);
    }

    /** DataTable JSON. */
    public function listing(Request $request): JsonResponse
    {
        return response()->json($this->leads->datatable($request));
    }

    /** Inline status change from the triage table. */
    public function updateStatus(Request $request, Lead $lead): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(LeadStatus::class)],
        ]);

        $lead = $this->leads->updateStatus($lead, $validated['status']);

        return response()->json([
            'message' => 'Status updated.',
            'status' => $lead->status->value,
            'status_label' => $lead->status->label(),
        ]);
    }
}
