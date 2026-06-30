<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Actions\Organizations\ProvisionOrganizationRoles;
use App\Enums\OrganizationStatus;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Self-service onboarding. Creates a PENDING organization plus its first admin
 * user (INACTIVE until a super admin approves), provisions the org's roles and
 * assigns tenant_admin. Sends email verification. All in one transaction.
 */
final class RegisterOrganizationAction
{
    public function __construct(
        private readonly ProvisionOrganizationRoles $provisionRoles,
    ) {}

    /**
     * @param  array{organization_name:string,name:string,email:string,password:string}  $data
     */
    public function handle(array $data): User
    {
        $user = DB::transaction(function () use ($data): User {
            $organization = Organization::create([
                'name' => $data['organization_name'],
                'slug' => $this->uniqueSlug($data['organization_name']),
                'status' => OrganizationStatus::Pending,
            ]);

            $user = new User([
                'organization_id' => $organization->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'], // hashed by the cast
                'role' => User::ROLE_TENANT_ADMIN,
            ]);
            $user->is_active = false; // activated on approval
            $user->save();

            $this->provisionRoles->handle($organization->id);
            $user->assignPrimaryRole(User::ROLE_TENANT_ADMIN);

            return $user;
        });

        // Queued verification email (outside the transaction).
        $user->sendEmailVerificationNotification();

        return $user;
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'org';
        $slug = $base;
        $i = 1;

        while (Organization::withoutGlobalScopes()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
