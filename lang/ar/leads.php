<?php

declare(strict_types=1);

return [
    'title' => 'العملاء المحتملون',
    'triage' => 'فرز العملاء المحتملين',
    'name' => 'الاسم',
    'email' => 'البريد الإلكتروني',
    'company' => 'الشركة',
    'created' => 'تاريخ الإنشاء',
    'status_label' => 'الحالة',
    'update_failed' => 'تعذّر التحديث',

    // Enum labels (App\Enums\LeadStatus) — values stay English.
    'status' => [
        'new' => 'جديد',
        'contacted' => 'تم التواصل',
        'qualified' => 'مؤهّل',
        'converted' => 'تم التحويل',
        'rejected' => 'مرفوض',
    ],
];
