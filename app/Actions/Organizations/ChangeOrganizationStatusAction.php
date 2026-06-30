<?php

declare(strict_types=1);

namespace App\Actions\Organizations;

use App\Enums\OrganizationStatus;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\OrganizationStatusChangedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * Transition an organization's lifecycle status and keep its users' active
 * flags consistent, then notify the org's admins. Used by the super-admin
 * onboarding screens.
 *
 *   pending -> approved   : activate admin users, they can now log in
 *   * -> suspended/rejected: deactivate all users (locked out)
 */
final class ChangeOrganizationStatusAction
{
    public function handle(Organization $organization, OrganizationStatus $status): Organization
    {
        DB::transaction(function () use ($organization, $status): void {
            $organization->status = $status;
            $organization->save();

            $isApproved = $status === OrganizationStatus::Approved;

            // Org-scoped user update without relying on ambient tenancy.
            User::query()
                ->where('organization_id', $organization->id)
                ->update(['is_active' => $isApproved]);
        });

        // Notify the organization's admins of the change (queued).
        $admins = User::query()
            ->where('organization_id', $organization->id)
            ->where('role', User::ROLE_TENANT_ADMIN)
            ->get();

        Notification::send($admins, new OrganizationStatusChangedNotification($organization->fresh()));

        return $organization;
    }
}
