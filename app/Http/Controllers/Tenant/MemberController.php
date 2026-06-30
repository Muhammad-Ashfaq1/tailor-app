<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveMemberRequest;
use App\Models\User;
use App\Support\DataTables\DataTableBuilder;
use App\Support\Impersonation\Impersonator;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Tenant member (user) management + tenant-admin -> member impersonation.
 * Members are users sharing the current organization_id.
 */
final readonly class MemberController extends Controller
{
    public function __construct(
        private Impersonator $impersonator,
    ) {}

    public function index(): View
    {
        return view('tenant.members.index', [
            'roles' => User::TENANT_ROLES,
        ]);
    }

    public function listing(Request $request): JsonResponse
    {
        $query = User::query()->where('organization_id', OrganizationContext::id());

        return response()->json(
            DataTableBuilder::for($query, $request)
                ->searchable(['name', 'email'])
                ->orderable(['id', 'name', 'email', 'role', 'created_at'])
                ->map(fn (User $user): array => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'role_label' => Str::headline((string) $user->role),
                    'is_active' => $user->is_active,
                    'is_self' => $user->id === auth()->id(),
                ])
                ->toArray()
        );
    }

    public function save(SaveMemberRequest $request): JsonResponse
    {
        $orgId = (int) OrganizationContext::id();
        $id = $request->filled('id') ? (int) $request->input('id') : null;

        $user = $id !== null
            ? User::where('organization_id', $orgId)->findOrFail($id)
            : new User(['organization_id' => $orgId]);

        $user->name = $request->string('name')->toString();
        $user->email = $request->string('email')->toString();
        $user->is_active = $request->boolean('is_active');
        $user->organization_id = $orgId;

        if ($request->filled('password')) {
            $user->password = $request->string('password')->toString(); // hashed by cast
        }

        if ($id === null) {
            $user->email_verified_at = now(); // admin-created members are pre-verified
        }

        $user->save();
        $user->assignPrimaryRole($request->string('role')->toString());

        return response()->json(['message' => 'Member saved.', 'member' => ['id' => $user->id]]);
    }

    /** tenant-admin -> member impersonation (shares the stop handler). */
    public function impersonate(Request $request, User $user): RedirectResponse
    {
        abort_unless((int) $user->organization_id === OrganizationContext::id(), 404);
        abort_if($user->id === $request->user()->id, 422, 'You cannot impersonate yourself.');
        abort_if($user->isTenantAdmin(), 422, 'You cannot impersonate another administrator.');

        $this->impersonator->start($user);

        return redirect()->route('member.dashboard')
            ->with('status', "You are now impersonating {$user->name}.");
    }
}
