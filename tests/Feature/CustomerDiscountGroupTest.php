<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Organizations\ProvisionOrganizationRoles;
use App\Models\Customer;
use App\Models\CustomerDiscountGroup;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerDiscountGroupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([PermissionSeeder::class, RolePermissionSeeder::class]);
    }

    private function tenantAdmin(Organization $org): User
    {
        app(ProvisionOrganizationRoles::class)->handle($org->id);

        $user = User::factory()->forOrganization($org, User::ROLE_TENANT_ADMIN)->create([
            'is_active' => true,
        ]);
        $user->assignPrimaryRole(User::ROLE_TENANT_ADMIN);

        return $user;
    }

    public function test_guest_is_redirected_from_discount_groups(): void
    {
        $this->get('/tenant/customer-discount-groups')->assertRedirect('/login');
    }

    public function test_tenant_admin_can_load_discount_groups_index(): void
    {
        $org = Organization::factory()->create();
        $admin = $this->tenantAdmin($org);

        $this->actingAs($admin)
            ->get('/tenant/customer-discount-groups')
            ->assertOk()
            ->assertSee(__('customer_discount_groups.title'));
    }

    public function test_listing_is_org_scoped(): void
    {
        $orgA = Organization::factory()->create();
        $adminA = $this->tenantAdmin($orgA);
        $groupA = CustomerDiscountGroup::factory()->forOrganization($orgA)->create([
            'name' => 'Org A VIP',
        ]);

        $orgB = Organization::factory()->create();
        $adminB = $this->tenantAdmin($orgB);
        $groupB = CustomerDiscountGroup::factory()->forOrganization($orgB)->create([
            'name' => 'Org B VIP',
        ]);

        // Admin B should not see Org A's group.
        $this->actingAs($adminB)
            ->getJson('/tenant/customer-discount-groups/listing?draw=1&start=0&length=50')
            ->assertOk()
            ->assertJsonMissing(['name' => 'Org A VIP'])
            ->assertJsonFragment(['name' => 'Org B VIP']);
    }

    public function test_tenant_admin_can_create_discount_group(): void
    {
        $org = Organization::factory()->create();
        $admin = $this->tenantAdmin($org);

        $this->actingAs($admin)
            ->postJson('/tenant/customer-discount-groups/save', [
                'name' => 'VIP Gold',
                'discount_percentage' => 15.50,
                'description' => '15.5% discount for Gold members',
                'is_active' => true,
            ])
            ->assertOk()
            ->assertJson(['message' => __('customer_discount_groups.alerts.saved')]);

        $this->assertDatabaseHas('customer_discount_groups', [
            'organization_id' => $org->id,
            'name' => 'VIP Gold',
            'discount_percentage' => 15.50,
            'is_active' => true,
        ]);
    }

    public function test_cannot_create_duplicate_group_name_in_same_org(): void
    {
        $org = Organization::factory()->create();
        $admin = $this->tenantAdmin($org);
        CustomerDiscountGroup::factory()->forOrganization($org)->create(['name' => 'VIP']);

        $this->actingAs($admin)
            ->postJson('/tenant/customer-discount-groups/save', [
                'name' => 'VIP',
                'discount_percentage' => 10,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_can_create_duplicate_group_name_in_different_orgs(): void
    {
        $orgA = Organization::factory()->create();
        CustomerDiscountGroup::factory()->forOrganization($orgA)->create(['name' => 'VIP']);

        $orgB = Organization::factory()->create();
        $adminB = $this->tenantAdmin($orgB);

        $this->actingAs($adminB)
            ->postJson('/tenant/customer-discount-groups/save', [
                'name' => 'VIP',
                'discount_percentage' => 10,
            ])
            ->assertOk();
    }

    public function test_tenant_admin_can_delete_discount_group_and_nullify_customers(): void
    {
        $org = Organization::factory()->create();
        $admin = $this->tenantAdmin($org);
        $group = CustomerDiscountGroup::factory()->forOrganization($org)->create();

        // Create customer in that group
        $customer = Customer::factory()->forOrganization($org)->create([
            'discount_group_id' => $group->id,
        ]);

        $this->actingAs($admin)
            ->deleteJson("/tenant/customer-discount-groups/{$group->id}")
            ->assertOk()
            ->assertJson(['message' => __('customer_discount_groups.alerts.deleted')]);

        $this->assertSoftDeleted('customer_discount_groups', ['id' => $group->id]);

        // Customer's discount_group_id should be nullified
        $customer->refresh();
        $this->assertNull($customer->discount_group_id);
    }
}
