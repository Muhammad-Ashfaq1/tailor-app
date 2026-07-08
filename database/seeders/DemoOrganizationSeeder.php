<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\Organizations\ProvisionOrganizationRoles;
use App\Enums\OrganizationStatus;
use App\Models\Customer;
use App\Models\CustomerDiscountGroup;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * A fully-populated demo tenant so every screen has data on first run.
 * Login: admin@acme.test / password (tenant admin), member@acme.test (member).
 */
class DemoOrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::query()->firstOrCreate(
            ['slug' => 'acme'],
            ['name' => 'Acme Inc', 'status' => OrganizationStatus::Approved],
        );

        app(ProvisionOrganizationRoles::class)->handle($organization->id);

        // Tenant users.
        $this->user($organization, 'Acme Admin', 'admin@acme.test', User::ROLE_TENANT_ADMIN);
        $this->user($organization, 'Morgan Manager', 'manager@acme.test', User::ROLE_MANAGER);
        $this->user($organization, 'Lee Lead', 'lead@acme.test', User::ROLE_MEMBER_LEAD);
        $this->user($organization, 'Casey Member', 'member@acme.test', User::ROLE_MEMBER);

        // Org-scoped data — initialize tenancy so org_id auto-fills.
        tenancy()->initialize($organization);

        // Demo discount groups (org-scoped).
        if (class_exists(CustomerDiscountGroup::class)) {
            $gold = CustomerDiscountGroup::factory()->create([
                'organization_id' => $organization->id,
                'name' => 'VIP Gold',
                'discount_percentage' => 15.00,
                'description' => 'Gold level customers - 15% discount',
            ]);
            $silver = CustomerDiscountGroup::factory()->create([
                'organization_id' => $organization->id,
                'name' => 'Silver',
                'discount_percentage' => 10.00,
                'description' => 'Regular loyal customers - 10% discount',
            ]);
            $bronze = CustomerDiscountGroup::factory()->create([
                'organization_id' => $organization->id,
                'name' => 'Bronze',
                'discount_percentage' => 5.00,
                'description' => 'Bronze tier - 5% discount',
            ]);

            // Demo API customers (org-scoped).
            if (class_exists(Customer::class)) {
                Customer::factory()->create([
                    'organization_id' => $organization->id,
                    'discount_group_id' => $gold->id,
                    'name' => 'John Doe',
                ]);
                Customer::factory()->create([
                    'organization_id' => $organization->id,
                    'discount_group_id' => $silver->id,
                    'name' => 'Jane Smith',
                ]);
                Customer::factory()->create([
                    'organization_id' => $organization->id,
                    'discount_group_id' => null,
                    'name' => 'Bob Johnson',
                ]);
            }
        }

        tenancy()->end();

        // Central leads for the super-admin triage screen.
        if (class_exists(Lead::class)) {
            Lead::factory()->count(8)->create();
        }
    }

    private function user(Organization $org, string $name, string $email, string $role): User
    {
        $user = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'organization_id' => $org->id,
                'name' => $name,
                'password' => 'password',
                'role' => $role,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        $user->assignPrimaryRole($role);

        return $user;
    }
}
