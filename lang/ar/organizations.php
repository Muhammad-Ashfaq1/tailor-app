<?php

declare(strict_types=1);

return [
    'title' => 'المنشآت',
    'new' => 'منشأة جديدة',
    'edit' => 'تعديل المنشأة',
    'name' => 'الاسم',
    'slug' => 'المعرّف',
    'users' => 'المستخدمون',
    'status_label' => 'الحالة',
    'created' => 'تاريخ الإنشاء',
    'impersonate_admin' => 'انتحال دخول المدير',
    'update_failed' => 'تعذّر التحديث',
    'ph_name' => 'مثال: خياط الأناقة',

    // Enum labels (App\Enums\OrganizationStatus) — values stay English.
    'status' => [
        'pending' => 'قيد المراجعة',
        'approved' => 'معتمدة',
        'suspended' => 'موقوفة',
        'rejected' => 'مرفوضة',
    ],
];
