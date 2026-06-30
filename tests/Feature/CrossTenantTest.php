<?php
declare(strict_types=1);
namespace Tests\Feature;
use App\Actions\Organizations\ProvisionOrganizationRoles;
use App\Models\{Organization,Project,User};
use Database\Seeders\{PermissionSeeder,RolePermissionSeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class CrossTenantTest extends TestCase {
    use RefreshDatabase;
    private function admin(): User {
        $o=Organization::factory()->create(['status'=>'approved']);
        app(ProvisionOrganizationRoles::class)->handle($o->id);
        $u=User::factory()->forOrganization($o,'tenant_admin')->create();
        $u->assignPrimaryRole('tenant_admin');
        return $u;
    }
    public function test_two_requests(): void {
        $this->seed([PermissionSeeder::class, RolePermissionSeeder::class]);
        $a=$this->admin();
        $this->actingAs($a)->post('/tenant/projects/save',['name'=>'A Secret','status'=>'active'])->assertOk();
        $pA=Project::withoutOrganizationScope()->firstWhere('name','A Secret');
        fwrite(STDERR, "pA org={$pA->organization_id} tenant_after_req1=".(tenant()?->id ?? 'null')."\n");
        $b=$this->admin();
        fwrite(STDERR, "b org={$b->organization_id}\n");
        $resp=$this->actingAs($b)->get("/tenant/projects/{$pA->slug}");
        fwrite(STDERR, "STATUS=".$resp->status()." tenant_during=".(tenant()?->id ?? 'null')."\n");
        $resp->assertNotFound();
    }
}
