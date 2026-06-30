<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Repositories\Interface\LeadRepositoryInterface;
use App\Support\DataTables\DataTableBuilder;
use Illuminate\Http\Request;

final class LeadRepository extends BaseRepository implements LeadRepositoryInterface
{
    public function datatable(Request $request): array
    {
        // Leads are central — no global org scope applies here.
        $query = Lead::query();

        return DataTableBuilder::for($query, $request)
            ->searchable(['name', 'email', 'company'])
            ->orderable(['id', 'status', 'created_at'])
            ->map(fn (Lead $lead): array => [
                'id' => $lead->id,
                'name' => $lead->name,
                'email' => $lead->email,
                'company' => $lead->company,
                'message' => $lead->message,
                'status' => $lead->status->value,
                'status_label' => $lead->status->label(),
                'status_color' => $lead->status->color(),
                'created_at' => $lead->created_at?->toDateString(),
            ])
            ->toArray();
    }

    public function create(array $data): Lead
    {
        // Status is never client-supplied; new captures always start as 'new'.
        $data['status'] = LeadStatus::New->value;

        $lead = new Lead;
        $lead->fill($data);
        $lead->save();

        return $lead->refresh();
    }

    public function updateStatus(Lead $lead, string $status): Lead
    {
        $lead->status = LeadStatus::from($status);
        $lead->save();

        return $lead->refresh();
    }
}
