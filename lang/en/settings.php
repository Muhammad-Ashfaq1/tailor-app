<?php

declare(strict_types=1);

return [
    'title' => 'Settings',

    // Tabs
    'tabs' => [
        'profile' => 'Shop Profile',
        'regional' => 'Regional & Billing',
        'operations' => 'Operations',
        'notifications' => 'Notifications & Loyalty',
        'invoice' => 'Order & Invoice',
        'roles' => 'Roles & Permissions',
    ],

    // Profile section
    'shop_name' => 'Shop name',
    'business_name' => 'Business name',
    'owner_name' => 'Owner name',
    'website_url' => 'Website',
    'business_email' => 'Business email',
    'business_phone' => 'Business phone',
    'address' => 'Address',
    'city' => 'City',
    'state' => 'State / Region',
    'country' => 'Country',

    // Regional section
    'locale' => 'Language',
    'timezone' => 'Timezone',
    'date_format' => 'Date format',
    'time_format' => 'Time format',
    'first_day_of_week' => 'First day of week',
    'currency' => 'Currency',
    'currency_symbol' => 'Currency symbol',
    'currency_position' => 'Currency position',
    'currency_decimals' => 'Decimal places',

    // Operations section
    'default_stitching_type' => 'Default stitching type',
    'measurement_unit' => 'Measurement unit',
    'default_delivery_type' => 'Default delivery type',
    'home_delivery_charge' => 'Home delivery charge',

    // Loyalty section
    'default_credit_type' => 'Default credit reward',
    'default_credit_value' => 'Default credit value',

    // Invoice section
    'prefix' => 'Invoice prefix',
    'next_number' => 'Next invoice number',
    'pad_length' => 'Number padding',
    'payment_terms_days' => 'Payment terms (days)',
    'tax_rate' => 'Tax rate (%)',
    'footer_notes' => 'Invoice footer notes',

    // Notifications matrix
    'notifications_title' => 'Notification events',
    'channel_email' => 'Email',
    'channel_in_app' => 'In-app',
    'event' => 'Event',
    'events' => [
        'order_placed' => 'Order placed',
        'order_ready' => 'Order ready',
        'order_delivered' => 'Order delivered',
        'payment_received' => 'Payment received',
        'measurement_updated' => 'Measurement updated',
    ],

    // Section headings
    'shop_information' => 'Shop Information',
    'contact_details' => 'Contact Details',
    'business_address' => 'Business Address',
    'regional_heading' => 'Regional',
    'currency_heading' => 'Currency',
    'operational_defaults' => 'Operational Defaults',
    'invoice_numbering' => 'Invoice Numbering',
    'order_payment' => 'Order & Payment',
    'notifications_heading' => 'Notifications',
    'loyalty_heading' => 'Loyalty',

    // Field labels (as shown in blade)
    'currency_code' => 'Code (ISO)',
    'currency_symbol_short' => 'Symbol',
    'currency_position_short' => 'Symbol position',

    // Helper / descriptive text
    'notifications_help' => 'Choose how each event notifies you.',
    'loyalty_help' => 'Default credit reward applied to new customers (editable per customer).',
    'save_settings' => 'Save Settings',
    'website_url_placeholder' => 'https://…',
    'business_email_placeholder' => 'name@example.com',
    'business_phone_placeholder' => '05XXXXXXXX',
    'stitching_type_placeholder' => 'e.g. Saudi',
    'footer_notes_placeholder' => 'Shown at the bottom of every invoice…',

    // Loyalty credit types
    'credit_type_none' => 'No credit',
    'credit_type_percentage' => 'Percentage',
    'credit_type_fixed' => 'Fixed amount',

    // Invoice info banner
    'invoice_currency_notice_before' => 'Invoices use the currency set under',
    'invoice_currency_notice_after' => 'currently',

    // Roles section
    'roles_description' => 'Manage who can do what in your shop — roles and their permissions are configured on the dedicated screen.',
    'roles_manage_button' => 'Manage roles & permissions',
    'roles_no_permission' => "You don't have permission to manage roles.",
];
