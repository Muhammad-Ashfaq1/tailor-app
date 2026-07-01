<?php

declare(strict_types=1);

return [
    'title' => 'Organizations',
    'new' => 'New Organization',
    'edit' => 'Edit Organization',
    'name' => 'Name',
    'slug' => 'Slug',
    'users' => 'Users',
    'status_label' => 'Status',
    'created' => 'Created',
    'impersonate_admin' => 'Impersonate admin',
    'update_failed' => 'Could not update',

    // Enum labels (App\Enums\OrganizationStatus) — values stay English.
    'status' => [
        'pending' => 'Pending review',
        'approved' => 'Approved',
        'suspended' => 'Suspended',
        'rejected' => 'Rejected',
    ],
];
