<?php

declare(strict_types=1);

return [
    'title' => 'مجموعات خصم العملاء',
    'customer_discount_group' => 'مجموعة خصم العميل',
    'new' => 'مجموعة خصم جديدة',
    'create' => 'إنشاء مجموعة خصم',
    'edit' => 'تعديل مجموعة الخصم',
    'delete_confirm' => 'هل أنت متأكد من حذف مجموعة الخصم هذه؟ سيتم إزالة هذه المجموعة من أي عملاء مرتبطين بها.',
    'load_failed' => 'تعذّر تحميل مجموعة الخصم',
    'delete_failed' => 'تعذّر حذف مجموعة الخصم',

    // Fields
    'fields' => [
        'name' => 'الاسم',
        'discount_percentage' => 'نسبة الخصم (%)',
        'description' => 'الوصف',
        'status' => 'الحالة',
        'active' => 'نشط',
        'inactive' => 'غير نشط',
    ],

    // Placeholders
    'placeholders' => [
        'name' => 'مثال: ذهبي VIP',
        'discount_percentage' => '10.00',
        'description' => 'صف فوائد هذه المجموعة هنا...',
    ],

    // Alerts
    'alerts' => [
        'saved' => 'تم حفظ مجموعة الخصم بنجاح.',
        'deleted' => 'تم حذف مجموعة الخصم بنجاح.',
    ],
];
