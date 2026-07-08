<?php

declare(strict_types=1);

return [
    'title' => 'Customer Discount Groups',
    'customer_discount_group' => 'Customer Discount Group',
    'new' => 'New Discount Group',
    'create' => 'Create Discount Group',
    'edit' => 'Edit Discount Group',
    'delete_confirm' => 'Delete this discount group? Any associated customers will have their discount group removed.',
    'load_failed' => 'Could not load discount group',
    'delete_failed' => 'Could not delete discount group',

    // Fields
    'fields' => [
        'name' => 'Name',
        'discount_percentage' => 'Discount Percentage (%)',
        'description' => 'Description',
        'status' => 'Status',
        'active' => 'Active',
        'inactive' => 'Inactive',
    ],

    // Placeholders
    'placeholders' => [
        'name' => 'e.g. VIP Gold',
        'discount_percentage' => '10.00',
        'description' => 'Describe the benefits of this group...',
    ],

    // Alerts
    'alerts' => [
        'saved' => 'Discount group saved successfully.',
        'deleted' => 'Discount group deleted successfully.',
    ],
];
