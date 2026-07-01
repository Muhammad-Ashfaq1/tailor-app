<?php

declare(strict_types=1);

return [
    'title' => 'Leads',
    'triage' => 'Lead triage',
    'name' => 'Name',
    'email' => 'Email',
    'company' => 'Company',
    'created' => 'Created',
    'status_label' => 'Status',
    'update_failed' => 'Could not update',

    // Enum labels (App\Enums\LeadStatus) — values stay English.
    'status' => [
        'new' => 'New',
        'contacted' => 'Contacted',
        'qualified' => 'Qualified',
        'converted' => 'Converted',
        'rejected' => 'Rejected',
    ],
];
