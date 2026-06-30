<?php
declare(strict_types=1);
namespace Tests\Feature;
use App\Actions\Organizations\ProvisionOrganizationRoles;
use App\Models\{Lead,Organization,Project,User};
use Database\Seeders\{PermissionSeeder,RolePermissionSeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class ReportsLeadsTest extends TestCase {
    use RefreshDatabase;
    protected function setUp(): void { parent::setUp(); $this->seed([PermissionSeeder::class, RolePermissionSeeder::class]); }
    public function test_lead_capture_is_central(): void {
        $this->postJson('/leads',['name'=>'Jo','email'=>'jo@x.com','company'=>'X','message'=>'hi'])->assertOk();
        $this->assertDatabaseHas('leads',['email'=>'jo@x.com','status'=>'new']);
    }
    public function test_tenant_report_listing_and_export(): void {
        $o=Organization::factory()->create(['status'=>'approved']);
        app(ProvisionOrganizationRoles::class)->handle($o->id);
        $a=User::factory()->forOrganization($o,'tenant_admin')->create();
        $a->assignPrimaryRole('tenant_admin');
        $this->actingAs($a)->get('/tenant/reports/projects')->assertOk();
        $this->actingAs($a)->get('/tenant/reports/projects/listing?draw=1&start=0&length=10')
            ->assertOk()->assertJsonStructure(['draw','recordsTotal','data']);
        $export=$this->actingAs($a)->get('/tenant/reports/projects/export');
        $export->assertOk();
        $this->assertStringContainsString('spreadsheet', $export->headers->get('content-type'));
    }
}
