<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_pages_render_and_save_persists(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@acme.test')->firstOrFail();
        $org = Organization::findOrFail($admin->organization_id);

        // Every route-based settings tab renders.
        foreach (['profile', 'regional', 'operations', 'notifications', 'invoice', 'roles'] as $page) {
            $this->actingAs($admin)->get("/tenant/settings/{$page}")->assertOk();
        }

        // Shop Profile: shop_name updates the org name, the rest lands in the JSON store.
        $this->actingAs($admin)
            ->postJson('/tenant/settings/save/profile', [
                'shop_name' => 'Riyadh Tailors',
                'business_name' => 'Riyadh Tailors LLC',
                'owner_name' => 'Sasha',
                'business_email' => 'shop@riyadh.test',
            ])
            ->assertOk()
            ->assertJson(['message' => 'Settings saved.']);

        $org->refresh();
        $this->assertSame('Riyadh Tailors', $org->name);
        $this->assertSame('Riyadh Tailors LLC', $org->mergedSettings()['profile']['business_name']);

        // Regional (incl. currency) persists.
        $this->actingAs($admin)
            ->postJson('/tenant/settings/save/regional', [
                'locale' => 'ar',
                'timezone' => 'Asia/Riyadh',
                'date_format' => 'd M Y',
                'time_format' => 'h:i A',
                'first_day_of_week' => 'sunday',
                'currency' => 'SAR',
                'currency_symbol' => 'SAR',
                'currency_position' => 'after',
                'currency_decimals' => 3,
            ])
            ->assertOk();

        $this->assertSame(3, $org->fresh()->mergedSettings()['regional']['currency_decimals']);

        // Notifications matrix persists per-channel booleans.
        $this->actingAs($admin)
            ->postJson('/tenant/settings/notifications', [
                'events' => ['order_placed' => ['email' => '1', 'in_app' => '0']],
            ])
            ->assertOk();

        $events = $org->fresh()->mergedSettings()['notifications']['events'];
        $this->assertTrue($events['order_placed']['email']);
        $this->assertFalse($events['order_placed']['in_app']);

        // Validation errors come back in the standard 422 JSON shape.
        $this->actingAs($admin)
            ->postJson('/tenant/settings/save/profile', ['shop_name' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['shop_name', 'business_name', 'owner_name']);
    }
}
