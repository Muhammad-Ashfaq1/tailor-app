<?php

declare(strict_types=1);

namespace App\Repositories\Interface;

use App\Models\Lead;
use Illuminate\Http\Request;

interface LeadRepositoryInterface
{
    /** Server-side DataTables payload for the lead triage listing. */
    public function datatable(Request $request): array;

    /** Capture a new central lead; status is forced to 'new'. */
    public function create(array $data): Lead;

    /** Move a lead to a new triage status; returns the saved model. */
    public function updateStatus(Lead $lead, string $status): Lead;
}
