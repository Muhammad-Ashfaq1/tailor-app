<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveRoleRequest;
use App\Models\User;
use App\Support\DataTables\DataTableBuilder;
use App\Support\Permissions\PermissionCatalog;
use App\Support\Permissions\PermissionTeamScope;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

/**
 * Roles UI for a tenant. Roles are spatie team-scoped on organization_id.
 * super_admin & tenant_admin are PROTECTED: their name is locked and they
 * cannot be deleted (permissions remain editable).
 */
final readonly class RoleController extends Controller
{
    public function index(): View
    {
        return view('tenant.roles.index', [
            'permissionGroups' => $this->groupedPermissions(),
            'protectedRoles' => User::PROTECTED_ROLES,
        ]);
    }

    public function listing(Request $request): JsonResponse
    {
        $query = Role::query()
            ->where('team_id', OrganizationContext::id())
            ->with('permissions:id,name')
            ->withCount('permissions');

        return response()->json(
            DataTableBuilder::for($query, $request)
                ->searchable(['name'])
                ->orderable(['id', 'name'])
                ->map(fn (Role $role): array => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'permissions_count' => $role->permissions_count,
                    'permissions' => $role->permissions->pluck('name')->all(),
                    'protected' => in_array($role->name, User::PROTECTED_ROLES, true),
                ])
                ->toArray()
        );
    }

    public function save(SaveRoleRequest $request): JsonResponse
    {
        $orgId = (int) OrganizationContext::id();
        $permissions = $request->input('permissions', []);

        return PermissionTeamScope::for($orgId, function () use ($request, $orgId, $permissions): JsonResponse {
            $id = $request->filled('id') ? (int) $request->input('id') : null;

            $role = $id !== null
                ? Role::where('team_id', $orgId)->findOrFail($id)
                : new Role(['guard_name' => 'web', 'team_id' => $orgId]);

            $isProtected = in_array($role->name, User::PROTECTED_ROLES, true);

            // Protected roles keep their name; new/regular roles take the input.
            if (! $isProtected) {
                $role->name = $request->string('name')->toString();
            }
            $role->team_id = $orgId;
            $role->guard_name = 'web';
            $role->save();

            $role->syncPermissions($permissions);

            return response()->json(['message' => 'Role saved.', 'role' => ['id' => $role->id, 'name' => $role->name]]);
        });
    }

    public function destroy(Role $role): JsonResponse
    {
        abort_unless((int) $role->team_id === OrganizationContext::id(), 404);
        abort_if(in_array($role->name, User::PROTECTED_ROLES, true), 422, 'This role is protected and cannot be deleted.');

        $role->delete();

        return response()->json(['message' => 'Role deleted.']);
    }

    /** @return array<string, array<int, string>> */
    private function groupedPermissions(): array
    {
        $groups = [];
        foreach (PermissionCatalog::all() as $permission) {
            $resource = explode('.', $permission)[0];
            $groups[$resource][] = $permission;
        }

        return $groups;
    }
}
