<?php
return [
    'statuses' => [
        'pending_approval' => ['بانتظار موافقة المدير', 'warning'],
        'rejected'         => ['مرفوض', 'danger'],
        'assigned'         => ['بانتظار التكنيكال', 'info'],
        'in_progress'      => ['جاري الإصلاح', 'primary'],
        'resolved'         => ['تم الحل', 'success'],
    ],
    'categories' => ['repair' => 'عطل / إصلاح جهاز', 'supplies' => 'طلب مستلزمات'],
    'items' => ['printer' => 'طابعة', 'keyboard' => 'كيبورد', 'mouse' => 'ماوس', 'ink' => 'حبر طابعة', 'other' => 'أخرى'],
    'priorities' => ['low' => 'منخفضة', 'normal' => 'عادية', 'high' => 'عالية', 'urgent' => 'عاجلة'],
    // المدة المستهدفة لحل التيكيت بالساعات حسب الأولوية (SLA)
    'sla_hours' => ['urgent' => 4, 'high' => 8, 'normal' => 24, 'low' => 72],
    'backup_keep' => 14, // عدد النسخ الاحتياطية المحفوظة
    'audit_actions' => [
        'created' => 'إنشاء', 'updated' => 'تعديل', 'deleted' => 'حذف', 'login' => 'تسجيل دخول', 'logout' => 'تسجيل خروج',
        'login_failed' => 'محاولة دخول فاشلة', 'permissions.updated' => 'تعديل صلاحيات', 'devices.imported' => 'استيراد أجهزة',
        'sectors.departments' => 'تعيين إدارات لقطاع', 'backup.created' => 'نسخة احتياطية', 'backup.downloaded' => 'تحميل نسخة احتياطية', 'backup.deleted' => 'حذف نسخة احتياطية',
    ],
    'audit_subjects' => ['User' => 'مستخدم', 'Department' => 'إدارة', 'Sector' => 'قطاع', 'Device' => 'جهاز', 'Ticket' => 'تيكيت', 'Role' => 'دور'],
    // تنبيه الجهاز كثير الأعطال: عدد تيكيتات «عطل» خلال عدد الأيام المحدد يوصي بمراجعة الجهاز أو استبداله
    'device_alert' => ['repairs' => 3, 'days' => 180],
    // عنوان النظام كما يصل إليه الموظفون (يُستخدم داخل رموز QR). مثال: http://192.168.1.10 — إن تُرك فارغا يُستخدم عنوان الصفحة الحالية
    'public_url' => env('HELPDESK_PUBLIC_URL'),
    'country_code' => '20', // مفتاح الدولة لأرقام واتساب (مصر 20)
    'buildings' => ['A' => 'مبنى A', 'B' => 'مبنى B', 'C' => 'مبنى C'],
    'permissions' => [
        'users.manage' => 'إدارة المستخدمين والإدارات',
        'roles.manage' => 'إدارة الصلاحيات',
        'tickets.create' => 'إنشاء تيكيت',
        'tickets.approve' => 'قبول/رفض التيكيت وتعيين التكنيكال (مدير عام النظم والمعلومات)',
        'tickets.view_department' => 'عرض تيكيتات إدارته',
        'tickets.delete' => 'حذف التيكيتات',
        'tickets.create_for_others' => 'إنشاء تيكيت لأي مستخدم في أي إدارة',
        'tickets.view_all' => 'عرض جميع التيكيتات',
        'devices.view' => 'عرض أجهزة الشركة',
        'devices.manage' => 'إدارة أجهزة الشركة',
        'devices.export' => 'تصدير أجهزة الشركة (Excel)',
        'devices.import' => 'استيراد أجهزة الشركة (Excel)',
        'system_devices.view' => 'عرض Computer ERP System',
        'system_devices.manage' => 'إدارة Computer ERP System',
        'system_devices.export' => 'تصدير Computer ERP System (Excel)',
        'system_devices.import' => 'استيراد Computer ERP System (Excel)',
        'reports.view' => 'عرض التقارير',
        'audit.view' => 'عرض سجل النشاط',
        'backup.manage' => 'إدارة النسخ الاحتياطي',
        'dashboard.view' => 'عرض لوحة التحكم',
    ],
    'roles' => [
        ['admin', 'مدير النظام', ['*']],
        ['it_gm', 'مدير عام النظم والمعلومات', ['dashboard.view','tickets.create','tickets.approve','tickets.delete','tickets.create_for_others','tickets.view_all','devices.view','devices.manage','devices.export','devices.import','system_devices.view','system_devices.manage','system_devices.export','system_devices.import','reports.view']],
        ['head_of_sector', 'رئيس قطاع', ['tickets.create','tickets.view_department','devices.view','reports.view']],
        ['general_manager', 'مدير عام', ['tickets.create','tickets.view_department','devices.view','reports.view']],
        ['dept_manager', 'مدير إدارة', ['tickets.create','tickets.view_department','devices.view','reports.view']],
        ['employee', 'موظف', ['tickets.create']],
        ['follower', 'متابع', ['tickets.view_all','devices.view','system_devices.view','reports.view']],
        ['technician', 'تكنيكال', ['tickets.create','devices.view','system_devices.view']],
    ],
];
