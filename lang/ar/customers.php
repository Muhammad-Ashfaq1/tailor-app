<?php

declare(strict_types=1);

return [
    'title' => 'العملاء',
    'customer' => 'عميل',
    'new' => 'عميل جديد',
    'create' => 'عميل جديد',
    'edit' => 'تعديل العميل',
    'delete_confirm' => 'حذف هذا العميل؟',
    'load_failed' => 'تعذّر تحميل بيانات العميل',
    'delete_failed' => 'تعذّر حذف العميل',

    // Fields
    'name' => 'الاسم',
    'phone' => 'الجوال',
    'type' => 'النوع',
    'email' => 'البريد الإلكتروني',
    'email_hint' => '(اختياري — للدخول عبر التطبيق)',
    'credit' => 'الرصيد',
    'credit_reward' => 'مكافأة رصيد',
    'credit_type' => 'مكافأة رصيد',
    'credit_value' => 'قيمة الرصيد',
    'credit_hint_percentage' => '(٪)',
    'credit_hint_fixed' => '(مبلغ)',
    'address' => 'العنوان',
    'notes' => 'ملاحظات',
    'app_password' => 'كلمة مرور التطبيق',
    'app_password_hint' => '(اختياري)',
    'confirm_password' => 'تأكيد',
    'status' => 'الحالة',
    'active' => 'نشط',
    'inactive' => 'غير نشط',

    'empty' => 'لا يوجد عملاء بعد.',

    // Enum labels (App\Enums\CustomerType) — values stay English.
    'types' => [
        'walk_in' => 'زائر',
        'regular' => 'عميل دائم',
    ],
    // Enum labels (App\Enums\CustomerCreditType).
    'credit_types' => [
        'none' => 'بدون رصيد',
        'percentage' => 'نسبة مئوية',
        'fixed' => 'مبلغ ثابت',
    ],
];
