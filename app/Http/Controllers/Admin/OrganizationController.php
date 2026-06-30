<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Organizations\ChangeOrganizationStatusAction;
use App\Actions\Organizations\ProvisionOrganizationRoles;
use App\Enums\OrganizationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaveOrganizationRequest;
use App\Models\Organization;
use App\Models\User;
use App\Support\DataTables\DataTableBuilder;
use App\Support\Impersonation\Impersonator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Super-admin oversight of tenant organizations: listing, create/edit,
 * lifecycle status transitions, and super-admin -> tenant-admin impersonation.
 */
final readonly class OrganizationController extends Controller
{
    public function __construct(
        private ProvisionOrganizationRoles $provisionRoles,
        private ChangeOrganizationStatusAction $changeStatus,
        private Impersonator $impersonator,
    ) {}

    public function index(): View
    {
        return view('admin.organizations.index', [
            'statuses' => OrganizationStatus::options(),
        ]);
    }

    public function listing(Request $request): JsonResponse
    {
        // Organization is the tenant model — NOT org-scoped, so super admin sees all.
        $query = Organization::query()->withCount('users');

        return response()->json(
            DataTableBuilder::for($query, $request)
                ->searchable(['name', 'slug'])
                ->orderable(['id', 'name', 'status', 'created_at'])
                ->map(fn (Organization $org): array => [
                    'id' => $org->id,
                    'name' => $org->name,
                    'slug' => $org->slug,
                    'status' => $org->status->value,
                    'status_label' => $org->status->label(),
                    'status_color' => $org->status->color(),
                    'users_count' => $org->users_count,
                    'created_at' => $org->created_at?->toDateString(),
                ])
                ->toArray()
        );
    }

    public function show(Organization $organization): JsonResponse
    {
        return response()->json([
            'id' => $organization->id,
            'name' => $organization->name,
            'slug' => $organization->slug,
            'status' => $organization->status->value,
        ]);
    }

    public function save(SaveOrganizationRequest $request): JsonResponse
    {
        $id = $request->filled('id') ? (int) $request->input('id') : null;
        $creating = $id === null;

        $organization = $creating ? new Organization : Organization::findOrFail($id);
        $organization->name = $request->string('name')->toString();
        $organization->status = OrganizationStatus::from($request->string('status')->toString());

        if ($creating) {
            $organization->slug = $this->uniqueSlug($request->string('name')->toString());
        }
        $organization->save();

        if ($creating) {
            $this->provisionRoles->handle($organization->id);
        }

        return response()->json(['message' => 'Organization saved.', 'organization' => ['id' => $organization->id]]);
    }

    public function updateStatus(Request $request, Organization $organization): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(OrganizationStatus::class)],
        ]);

        $this->changeStatus->handle($organization, OrganizationStatus::from($validated['status']));

        return response()->json(['message' => 'Status updated.']);
    }

    /** super-admin -> tenant-admin impersonation. */
    public function impersonate(Organization $organization): RedirectResponse
    {
        $admin = User::query()
            ->where('organization_id', $organization->id)
            ->where('role', User::ROLE_TENANT_ADMIN)
            ->first();

        abort_if($admin === null, 404, 'This organization has no admin to impersonate.');

        $this->impersonator->start($admin);

        return redirect()->route('tenant.dashboard')
            ->with('status', "You are now impersonating {$organization->name}.");
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'org';
        $slug = $base;
        $i = 1;
        while (Organization::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
