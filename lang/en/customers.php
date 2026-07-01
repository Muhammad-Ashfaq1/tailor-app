<?php

declare(strict_types=1);

return [
    'title' => 'Customers',
    'customer' => 'Customer',
    'new' => 'New Customer',
    'create' => 'New Customer',
    'edit' => 'Edit Customer',
    'delete_confirm' => 'Delete this customer?',
    'load_failed' => 'Could not load customer',
    'delete_failed' => 'Could not delete customer',

    // Fields
    'name' => 'Name',
    'phone' => 'Phone',
    'type' => 'Type',
    'email' => 'Email',
    'email_hint' => '(optional — for app login)',
    'credit' => 'Credit',
    'credit_reward' => 'Credit reward',
    'credit_type' => 'Credit reward',
    'credit_value' => 'Credit value',
    'credit_hint_percentage' => '(%)',
    'credit_hint_fixed' => '(amount)',
    'address' => 'Address',
    'notes' => 'Notes',
    'app_password' => 'App password',
    'app_password_hint' => '(optional)',
    'confirm_password' => 'Confirm',
    'status' => 'Status',
    'active' => 'Active',
    'inactive' => 'Inactive',

    'empty' => 'No customers yet.',

    // Enum labels (App\Enums\CustomerType) — values stay English.
    'types' => [
        'walk_in' => 'Walk-in',
        'regular' => 'Regular',
    ],
    // Enum labels (App\Enums\CustomerCreditType).
    'credit_types' => [
        'none' => 'No credit',
        'percentage' => 'Percentage',
        'fixed' => 'Fixed amount',
    ],
];
