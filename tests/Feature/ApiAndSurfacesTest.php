<?php
declare(strict_types=1);
namespace Tests\Feature;
use App\Actions\Organizations\ProvisionOrganizationRoles;
use App\Models\{Customer,Organization,User};
use Database\Seeders\{PermissionSeeder,RolePermissionSeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
class ApiAndSurfacesTest extends TestCase {
    use RefreshDatabase;
    protected function setUp(): void { parent::setUp(); $this->seed([PermissionSeeder::class, RolePermissionSeeder::class]); }

    public function test_super_admin_surfaces(): void {
        $su = User::factory()->superAdmin()->create();
        foreach (['/admin/dashboard','/admin/organizations','/admin/leads','/admin/reports'] as $p)
            $this->actingAs($su)->get($p)->assertOk();
    }
    public function test_member_surfaces(): void {
        $o=Organization::factory()->create(['status'=>'approved']);
        app(ProvisionOrganizationRoles::class)->handle($o->id);
        $m=User::factory()->forOrganization($o,'member')->create();
        $m->assignPrimaryRole('member');
        $this->actingAs($m)->get('/member/dashboard')->assertOk();
        $this->actingAs($m)->get('/member/reports')->assertOk();
    }
    public function test_api_login_and_scoped_access(): void {
        $o=Organization::factory()->create(['status'=>'approved','slug'=>'acme-api']);
        $c=Customer::factory()->forOrganization($o)->create(['email'=>'c@acme.test','password'=>Hash::make('password')]);
        $resp=$this->postJson('/api/v1/login',['organization'=>'acme-api','email'=>'c@acme.test','password'=>'password']);
        $resp->assertOk()->assertJsonStructure(['token']);
        $token=$resp->json('token');
        $this->withToken($token)->getJson('/api/v1/me')->assertOk();
    }
    public function test_api_login_rejects_unapproved_org(): void {
        $o=Organization::factory()->create(['status'=>'pending','slug'=>'pend-api']);
        Customer::factory()->forOrganization($o)->create(['email'=>'c2@p.test','password'=>Hash::make('password')]);
        $this->postJson('/api/v1/login',['organization'=>'pend-api','email'=>'c2@p.test','password'=>'password'])
            ->assertStatus(422);
    }
}
