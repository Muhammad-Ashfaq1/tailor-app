<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Organizations\ProvisionOrganizationRoles;
use App\Enums\OrganizationStatus;
use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([PermissionSeeder::class, RolePermissionSeeder::class]);
    }

    private function tenantAdmin(OrganizationStatus $status = OrganizationStatus::Approved): User
    {
        $org = Organization::factory()->create(['status' => $status]);
        app(ProvisionOrganizationRoles::class)->handle($org->id);

        $user = User::factory()->forOrganization($org, User::ROLE_TENANT_ADMIN)->create([
            'is_active' => $status === OrganizationStatus::Approved,
        ]);
        $user->assignPrimaryRole(User::ROLE_TENANT_ADMIN);

        return $user;
    }

    public function test_guest_is_redirected_from_tenant_area(): void
    {
        $this->get('/tenant/dashboard')->assertRedirect('/login');
    }

    public function test_guest_pages_render(): void
    {
        $this->get('/')->assertOk();
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
    }

    public function test_tenant_admin_can_load_every_panel(): void
    {
        $admin = $this->tenantAdmin();

        foreach ([
            '/tenant/dashboard',
            '/tenant/projects',
            '/tenant/tasks',
            '/tenant/members',
            '/tenant/roles',
            '/tenant/settings',
            '/tenant/reports',
        ] as $path) {
            $this->actingAs($admin)->get($path)->assertOk();
        }
    }

    public function test_project_save_is_org_scoped_and_audited(): void
    {
        $admin = $this->tenantAdmin();

        $this->actingAs($admin)
            ->post('/tenant/projects/save', ['name' => 'Launch Site', 'status' => 'active'])
            ->assertOk()
            ->assertJsonStructure(['message', 'project' => ['id', 'name']]);

        $project = Project::withoutOrganizationScope()->firstWhere('name', 'Launch Site');
        $this->assertSame($admin->organization_id, $project->organization_id);
        $this->assertSame($admin->id, $project->created_by);
        $this->assertSame('launch-site', $project->slug);
    }

    public function test_cross_tenant_route_binding_is_blocked(): void
    {
        $adminA = $this->tenantAdmin();
        $this->actingAs($adminA)->post('/tenant/projects/save', ['name' => 'A Secret', 'status' => 'active']);
        $projectA = Project::withoutOrganizationScope()->firstWhere('name', 'A Secret');

        $adminB = $this->tenantAdmin();
        // Org B admin cannot resolve org A's project (org-scoped route binding -> 404).
        $this->actingAs($adminB)->get("/tenant/projects/{$projectA->slug}")->assertNotFound();
    }

    public function test_pending_org_cannot_log_in(): void
    {
        $admin = $this->tenantAdmin(OrganizationStatus::Pending);
        $admin->forceFill(['email_verified_at' => now()])->save();

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_permission_blocks_unauthorized_action(): void
    {
        $org = Organization::factory()->create(['status' => OrganizationStatus::Approved]);
        app(ProvisionOrganizationRoles::class)->handle($org->id);
        $member = User::factory()->forOrganization($org, User::ROLE_MEMBER)->create();
        $member->assignPrimaryRole(User::ROLE_MEMBER);

        // Members have no projects.create permission -> 403 from route middleware.
        $this->actingAs($member)->post('/tenant/projects/save', ['name' => 'Nope', 'status' => 'active'])
            ->assertForbidden();
    }
}
