<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Models\Organization;

/**
 * Apply a set of dot-path settings updates onto an organization's settings
 * JSON column (merging over what's stored). Optionally updates the org name.
 */
final class UpdateOrganizationSettings
{
    /**
     * @param  array<string, mixed>  $dotUpdates  e.g. ['regional.currency' => 'EUR']
     */
    public function handle(Organization $organization, array $dotUpdates, ?string $name = null): Organization
    {
        $settings = $organization->settings ?? [];

        foreach ($dotUpdates as $path => $value) {
            data_set($settings, $path, $value);
        }

        $organization->settings = $settings;

        if ($name !== null) {
            $organization->name = $name;
        }

        $organization->save();

        return $organization;
    }
}
