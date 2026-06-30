<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Organizations\ProvisionOrganizationRoles;
use App\Enums\OrganizationStatus;
use App\Models\Organization;
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
            '/tenant/members',
            '/tenant/roles',
            '/tenant/settings',
            '/tenant/reports',
        ] as $path) {
            $this->actingAs($admin)->get($path)->assertOk();
        }
    }

    public function test_member_listing_is_org_scoped(): void
    {
        // A member that belongs to org A.
        $adminA = $this->tenantAdmin();
        $secretEmail = $adminA->email;

        // Org B admin must never see org A's users in the members listing.
        $adminB = $this->tenantAdmin();
        $this->actingAs($adminB)
            ->getJson('/tenant/members/listing?draw=1&start=0&length=50')
            ->assertOk()
            ->assertJsonMissing(['email' => $secretEmail]);
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

        // Plain members have no members.view permission -> 403 from route middleware.
        $this->actingAs($member)->get('/tenant/members')->assertForbidden();
    }
}
