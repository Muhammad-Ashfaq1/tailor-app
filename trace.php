<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Organization;
use App\Models\User;
use App\Actions\Organizations\ProvisionOrganizationRoles;

// Migrate and seed database first
echo "Migrating...\n";
\Illuminate\Support\Facades\Artisan::call('migrate:fresh');
echo "Seeding...\n";
\Illuminate\Support\Facades\Artisan::call('db:seed');

// Run the setup steps with PENDING organization
$org = Organization::factory()->create();

// Create tenant admin
app(ProvisionOrganizationRoles::class)->handle($org->id);
$admin = User::factory()->forOrganization($org, User::ROLE_TENANT_ADMIN)->create([
    'is_active' => true,
]);
$admin->assignPrimaryRole(User::ROLE_TENANT_ADMIN);

// Login
auth()->login($admin);

// Send GET request to /tenant/customer-discount-groups
$request = Illuminate\Http\Request::create('/tenant/customer-discount-groups', 'GET');

try {
    echo "Sending request to /tenant/customer-discount-groups...\n";
    $response = $kernel->handle($request);
    echo "Response status: " . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() >= 400 || isset($response->exception)) {
        if (isset($response->exception)) {
            throw $response->exception;
        }
    }
} catch (\Throwable $e) {
    echo "EXCEPTION THROWN:\n";
    echo $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " line " . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
