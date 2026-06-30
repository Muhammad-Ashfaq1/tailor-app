<?php
declare(strict_types=1);
namespace Tests\Feature;
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
}
