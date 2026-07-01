<?php

declare(strict_types=1);

return [
    'title' => 'الإعدادات',

    // Tabs
    'tabs' => [
        'profile' => 'ملف المتجر',
        'regional' => 'الإعدادات الإقليمية والفوترة',
        'operations' => 'العمليات',
        'notifications' => 'الإشعارات والولاء',
        'invoice' => 'الطلب والفاتورة',
        'roles' => 'الأدوار والصلاحيات',
    ],

    // Profile section
    'shop_name' => 'اسم المتجر',
    'business_name' => 'الاسم التجاري',
    'owner_name' => 'اسم المالك',
    'website_url' => 'الموقع الإلكتروني',
    'business_email' => 'البريد الإلكتروني للنشاط',
    'business_phone' => 'هاتف النشاط',
    'address' => 'العنوان',
    'city' => 'المدينة',
    'state' => 'المنطقة',
    'country' => 'الدولة',

    // Regional section
    'locale' => 'اللغة',
    'timezone' => 'المنطقة الزمنية',
    'date_format' => 'صيغة التاريخ',
    'time_format' => 'صيغة الوقت',
    'first_day_of_week' => 'أول أيام الأسبوع',
    'currency' => 'العملة',
    'currency_symbol' => 'رمز العملة',
    'currency_position' => 'موضع العملة',
    'currency_decimals' => 'عدد المنازل العشرية',

    // Operations section
    'default_stitching_type' => 'نوع التفصيل الافتراضي',
    'measurement_unit' => 'وحدة القياس',
    'default_delivery_type' => 'نوع التسليم الافتراضي',
    'home_delivery_charge' => 'رسوم التوصيل للمنزل',

    // Loyalty section
    'default_credit_type' => 'مكافأة الرصيد الافتراضية',
    'default_credit_value' => 'قيمة الرصيد الافتراضية',

    // Invoice section
    'prefix' => 'بادئة الفاتورة',
    'next_number' => 'رقم الفاتورة التالي',
    'pad_length' => 'عدد خانات الترقيم',
    'payment_terms_days' => 'مدة السداد (أيام)',
    'tax_rate' => 'نسبة الضريبة (٪)',
    'footer_notes' => 'ملاحظات تذييل الفاتورة',

    // Notifications matrix
    'notifications_title' => 'أحداث الإشعارات',
    'channel_email' => 'البريد الإلكتروني',
    'channel_in_app' => 'داخل التطبيق',
    'event' => 'الحدث',
    'events' => [
        'order_placed' => 'تم إنشاء الطلب',
        'order_ready' => 'الطلب جاهز',
        'order_delivered' => 'تم تسليم الطلب',
        'payment_received' => 'تم استلام الدفعة',
        'measurement_updated' => 'تم تحديث المقاسات',
    ],

    // Section headings
    'shop_information' => 'معلومات المتجر',
    'contact_details' => 'بيانات التواصل',
    'business_address' => 'عنوان النشاط',
    'regional_heading' => 'الإعدادات الإقليمية',
    'currency_heading' => 'العملة',
    'operational_defaults' => 'الإعدادات الافتراضية للعمليات',
    'invoice_numbering' => 'ترقيم الفواتير',
    'order_payment' => 'الطلب والدفع',
    'notifications_heading' => 'الإشعارات',
    'loyalty_heading' => 'الولاء',

    // Field labels (as shown in blade)
    'currency_code' => 'الرمز (ISO)',
    'currency_symbol_short' => 'الرمز',
    'currency_position_short' => 'موضع الرمز',

    // Helper / descriptive text
    'notifications_help' => 'اختر طريقة إشعارك عند وقوع كل حدث.',
    'loyalty_help' => 'مكافأة الرصيد الافتراضية المطبّقة على العملاء الجدد (قابلة للتعديل لكل عميل).',
    'save_settings' => 'حفظ الإعدادات',
    'website_url_placeholder' => 'https://…',
    'stitching_type_placeholder' => 'مثال: سعودي',
    'footer_notes_placeholder' => 'يظهر في أسفل كل فاتورة…',

    // Loyalty credit types
    'credit_type_none' => 'بدون رصيد',
    'credit_type_percentage' => 'نسبة مئوية',
    'credit_type_fixed' => 'مبلغ ثابت',

    // Invoice info banner
    'invoice_currency_notice_before' => 'تستخدم الفواتير العملة المحددة في',
    'invoice_currency_notice_after' => 'حاليًا',

    // Roles section
    'roles_description' => 'تحكّم في صلاحيات العاملين في متجرك — تُدار الأدوار وصلاحياتها من الشاشة المخصصة.',
    'roles_manage_button' => 'إدارة الأدوار والصلاحيات',
    'roles_no_permission' => 'ليس لديك صلاحية لإدارة الأدوار.',
];
