<?php

/**
 * Commercial entitlement catalog for custom subscription builds.
 *
 * IMPORTANT: These keys gate UI/API surfaces only. Never disable nwidart Modules
 * via modules_statuses.json — hard class coupling would crash dependents.
 *
 * Platform core (always granted): product catalog, employees, establishments,
 * general settings, and the accounting ledger engine (posting stays available
 * for sold modules that need it; full Accounting UI is sold via finance_business).
 */
return [

    'currency' => 'SAR',

    /** Annual billing: charge this many months, grant a full 12-month term (10 = two months free). */
    'yearly_months_charged' => 10,

    /**
     * Operational foundation — always granted with any paid module, never billed.
     * Products, employees, branches, settings, and the ledger engine.
     */
    'platform' => [
        'key' => 'platform',
        'name_en' => 'Operational foundation',
        'name_ar' => 'الأساس التشغيلي',
        'description_en' => 'Products, employees, branches, and settings — included free with every module you choose.',
        'description_ar' => 'المنتجات والموظفون والفروع والإعدادات — مشمولة مجاناً مع أي وحدة تختارها.',
        'price_month' => 0,
        'always_included' => true,
        'billable' => false,
        'includes' => [
            'en' => ['Product catalog', 'Employees', 'Branches', 'Settings', 'Ledger engine'],
            'ar' => ['كتالوج المنتجات', 'الموظفون', 'الفروع', 'الإعدادات', 'محرك القيود المحاسبية'],
        ],
    ],

    'quotas' => [
        'employees' => [
            'key' => 'employees',
            'name_en' => 'Employees',
            'name_ar' => 'الموظفون',
            'min' => 1,
            'max' => 500,
            'included' => 5,
            'price_per_extra_month' => 25,
            'linked_module' => 'employee_schedule',
        ],
        'establishments' => [
            'key' => 'establishments',
            'name_en' => 'Branches',
            'name_ar' => 'الفروع',
            'min' => 1,
            'max' => 50,
            'included' => 1,
            'price_per_extra_month' => 99,
        ],
        'screen_devices' => [
            'key' => 'screen_devices',
            'name_en' => 'Screen devices',
            'name_ar' => 'أجهزة الشاشات',
            'min' => 1,
            'max' => 200,
            'included' => 1,
            'price_per_extra_month' => 49,
            'linked_module' => 'digital_screens',
        ],
        /** Billed per extra device beyond included; shown inside Cashier card. */
        'pos_devices' => [
            'key' => 'pos_devices',
            'name_en' => 'POS devices',
            'name_ar' => 'أجهزة الكاشير',
            'min' => 1,
            'max' => 20,
            'included' => 1,
            'price_per_extra_month' => 79,
            'linked_module' => 'cashier_pos',
        ],
        'waiter_seats' => [
            'key' => 'waiter_seats',
            'name_en' => 'Waiter app seats',
            'name_ar' => 'مقاعد تطبيق الويتر',
            'min' => 1,
            'max' => 50,
            'included' => 1,
            'price_per_extra_month' => 29,
            'linked_module' => 'waiter_app',
        ],
        'kitchen_devices' => [
            'key' => 'kitchen_devices',
            'name_en' => 'Kitchen devices',
            'name_ar' => 'أجهزة المطبخ',
            'min' => 1,
            'max' => 20,
            'included' => 1,
            'price_per_extra_month' => 49,
            'linked_module' => 'kitchen_app',
        ],
    ],

    /**
     * Sellable commercial modules.
     * requires: other module keys auto-selected when this one is chosen.
     * requires_any: at least one of these must be selected.
     * grants: legacy/internal keys unlocked when this module is purchased
     *         (used by tenant EntitlementGate for menu/route maps).
     * included_employees: seats bundled with the module (default 1). Added on top of
     *         the platform employees quota. Admins can change this per module.
     * includes: human-readable contents shown on the subscribe cards.
     */
    'modules' => [

        'cashier_pos' => [
            'key' => 'cashier_pos',
            'group' => 'front_of_house',
            'name_en' => 'Cashier & POS',
            'name_ar' => 'الكاشير ونقاط البيع',
            'description_en' => 'POS checkout, tables, and order flow at the counter.',
            'description_ar' => 'كاشير المبيعات والطاولات وتدفق الطلبات من نقطة البيع.',
            'price_month' => 299,
            'icon' => 'cash-register',
            'requires' => [],
            'inline_quota' => 'pos_devices',
            'includes' => [
                'en' => ['POS checkout', 'Tables & areas', 'Counter order flow'],
                'ar' => ['كاشير المبيعات', 'الطاولات والمناطق', 'تدفق طلبات العداد'],
            ],
            'menu_keys' => ['pos_roles', 'tables', 'areas'],
            'api_prefixes' => [
                'api/new-order',
                'api/tables',
            ],
        ],

        'waiter_app' => [
            'key' => 'waiter_app',
            'group' => 'front_of_house',
            'name_en' => 'Waiter app',
            'name_ar' => 'تطبيق الويتر',
            'description_en' => 'Mobile waiter ordering and table service (requires Cashier & POS).',
            'description_ar' => 'طلب الويتر وخدمة الطاولات من الجوال (يتطلب الكاشير).',
            'price_month' => 129,
            'icon' => 'device-phone',
            'requires' => ['cashier_pos'],
            'inline_quota' => 'waiter_seats',
            'includes' => [
                'en' => ['Waiter mobile ordering', 'Table service actions'],
                'ar' => ['طلبات الويتر من الجوال', 'خدمة الطاولات'],
            ],
            'menu_keys' => [],
            'api_prefixes' => [
                'api/waiter',
            ],
        ],

        'kitchen_app' => [
            'key' => 'kitchen_app',
            'group' => 'front_of_house',
            'name_en' => 'Kitchen display app',
            'name_ar' => 'تطبيق المطبخ',
            'description_en' => 'Kitchen order screen and ticket flow (requires Cashier & POS).',
            'description_ar' => 'شاشة طلبات المطبخ وتدفق التذاكر (يتطلب الكاشير).',
            'price_month' => 129,
            'icon' => 'fire',
            'requires' => ['cashier_pos'],
            'inline_quota' => 'kitchen_devices',
            'includes' => [
                'en' => ['Kitchen order board', 'Ticket status updates'],
                'ar' => ['لوحة طلبات المطبخ', 'تحديث حالة التذاكر'],
            ],
            'menu_keys' => [],
            'api_prefixes' => [
                'api/kitchen-orders',
            ],
        ],

        'electronic_menu' => [
            'key' => 'electronic_menu',
            'group' => 'front_of_house',
            'name_en' => 'Electronic menu',
            'name_ar' => 'المنيو الإلكتروني',
            'description_en' => 'QR menus, public ordering menu, and guest feedback.',
            'description_ar' => 'منيو QR والمنيو العام للعملاء وتقييمات الضيوف.',
            'price_month' => 199,
            'icon' => 'qr-code',
            'requires' => [],
            'includes' => [
                'en' => ['QR menu', 'Guest ordering', 'Feedback'],
                'ar' => ['منيو QR', 'طلب العميل', 'التقييمات'],
            ],
            'menu_keys' => ['tables_qr', 'menu_qr', 'menu_feedback', 'tables', 'areas'],
            'api_prefixes' => [
                'api/order',
            ],
        ],

        'digital_screens' => [
            'key' => 'digital_screens',
            'group' => 'front_of_house',
            'name_en' => 'Digital screens app',
            'name_ar' => 'تطبيق الشاشات',
            'description_en' => 'Branch display devices, playlists, and promo players.',
            'description_ar' => 'أجهزة عرض الفروع وقوائم التشغيل والإعلانات.',
            'price_month' => 149,
            'icon' => 'tv',
            'requires' => [],
            'inline_quota' => 'screen_devices',
            'includes' => [
                'en' => ['Screen devices', 'Playlists', 'Promo player'],
                'ar' => ['أجهزة الشاشات', 'قوائم التشغيل', 'مشغّل الإعلانات'],
            ],
            'menu_keys' => ['screen_module'],
            'api_prefixes' => [
                'api/v1/screen',
            ],
        ],

        'inventory' => [
            'key' => 'inventory',
            'group' => 'operations',
            'name_en' => 'Inventory',
            'name_ar' => 'المخزون',
            'description_en' => 'Stock levels, transfers, waste, warehouses, and inventory ops.',
            'description_ar' => 'أرصدة المخزون والتحويل والهدر والمستودعات وعمليات الجرد.',
            'price_month' => 199,
            'icon' => 'warehouse',
            'requires' => [],
            'includes' => [
                'en' => ['Stock & warehouses', 'Transfers', 'Waste / adjustments'],
                'ar' => ['المخزون والمستودعات', 'التحويلات', 'الهدر والتسويات'],
            ],
            'menu_keys' => ['inventory_module', 'inventory', 'transfer', 'waste', 'import'],
            'api_prefixes' => [],
        ],

        'manufacturing' => [
            'key' => 'manufacturing',
            'group' => 'operations',
            'name_en' => 'Manufacturing & prep',
            'name_ar' => 'التصنيع والتحضير',
            'description_en' => 'Recipes, prep lists, and light manufacturing (requires Inventory).',
            'description_ar' => 'الوصفات وقوائم التحضير والتصنيع الخفيف (يتطلب المخزون).',
            'price_month' => 149,
            'icon' => 'cog',
            'requires' => ['inventory'],
            'includes' => [
                'en' => ['Prep / recipes', 'Need-preparation lists', 'Light manufacturing'],
                'ar' => ['التحضير والوصفات', 'قوائم المطلوب تحضيره', 'تصنيع خفيف'],
            ],
            'menu_keys' => ['prep'],
            'api_prefixes' => [],
        ],

        'employee_schedule' => [
            'key' => 'employee_schedule',
            'group' => 'operations',
            'name_en' => 'Employee shifts',
            'name_ar' => 'دوامات الموظفين',
            'description_en' => 'Shift planning, timecards, attendance rules, and payroll scheduling.',
            'description_ar' => 'تخطيط الدوامات وبطاقات الحضور وقواعد الدوام وجدولة الرواتب.',
            'price_month' => 99,
            'icon' => 'calendar',
            'requires' => [],
            'inline_quota' => 'employees',
            'includes' => [
                'en' => ['Shift roster', 'Timecards', 'Payroll schedule'],
                'ar' => ['جدول الدوامات', 'بطاقات الحضور', 'جدولة الرواتب'],
            ],
            'menu_keys' => ['schedules', 'shift_schedule', 'payroll'],
            'api_prefixes' => [],
        ],

        'finance_business' => [
            'key' => 'finance_business',
            'group' => 'finance',
            'name_en' => 'Finance & business management',
            'name_ar' => 'المالية وإدارة الأعمال',
            'description_en' => 'One package: sales, purchases, accounting, and expenses — full commercial back office.',
            'description_ar' => 'باقة واحدة تشمل المبيعات والمشتريات والمحاسبة والمصروفات — المكتب الخلفي التجاري بالكامل.',
            'price_month' => 549,
            'icon' => 'briefcase',
            'requires' => [],
            'grants' => ['sales', 'purchases', 'accounting', 'expenses'],
            'includes' => [
                'en' => [
                    'Sales (quotations, invoices, returns, customers)',
                    'Purchases (orders, supplier invoices, returns)',
                    'Accounting (COA, journals, vouchers, cost centers)',
                    'Expenses workspace',
                ],
                'ar' => [
                    'المبيعات (عروض، فواتير، مرتجعات، عملاء)',
                    'المشتريات (أوامر، فواتير موردين، مرتجعات)',
                    'المحاسبة (شجرة حسابات، قيود، سندات، مراكز تكلفة)',
                    'المصروفات',
                ],
            ],
            'menu_keys' => [
                'sales',
                'purchases',
                'accounting_module',
                'clients_suppliers_module',
                'expenses_manage',
            ],
            'api_prefixes' => [
                'api/v1/invoices',
                'api/v1/coupons',
            ],
        ],

        'franchise' => [
            'key' => 'franchise',
            'group' => 'growth',
            'name_en' => 'Franchise',
            'name_ar' => 'الامتياز التجاري',
            'description_en' => 'Franchise companies, shared products, and menus.',
            'description_ar' => 'شركات الامتياز ومشاركة المنتجات والقوائم.',
            'price_month' => 299,
            'icon' => 'building',
            'requires' => [],
            'includes' => [
                'en' => ['Franchise companies', 'Shared products / menus'],
                'ar' => ['شركات الامتياز', 'منتجات وقوائم مشتركة'],
            ],
            'menu_keys' => ['franchise'],
            'api_prefixes' => [],
        ],

        'reports' => [
            'key' => 'reports',
            'group' => 'growth',
            'name_en' => 'Advanced reports',
            'name_ar' => 'التقارير المتقدمة',
            'description_en' => 'Operational reports for the modules you buy. Not useful alone.',
            'description_ar' => 'التقارير التشغيلية للوحدات التي تشتريها. لا تعمل وحدها بدون وحدات بيانات.',
            'price_month' => 99,
            'icon' => 'chart',
            'requires' => [],
            'requires_any' => [
                'finance_business',
                'inventory',
                'manufacturing',
                'cashier_pos',
                'sales',
                'purchases',
                'accounting',
            ],
            'includes' => [
                'en' => ['Sales / purchases / inventory reports', 'Operational dashboards'],
                'ar' => ['تقارير المبيعات والمشتريات والمخزون', 'لوحات تشغيلية'],
            ],
            'menu_keys' => ['reports_module'],
            'api_prefixes' => [],
        ],
    ],

    /**
     * Legacy module keys kept for old subscriptions.
     * Not sold in the new builder (seeder deactivates them).
     */
    'legacy_modules' => ['sales', 'purchases', 'accounting', 'expenses'],

    'groups' => [
        'front_of_house' => [
            'name_en' => 'Front of house & apps',
            'name_ar' => 'واجهة العملاء والتطبيقات',
        ],
        'operations' => [
            'name_en' => 'Operations',
            'name_ar' => 'التشغيل',
        ],
        'finance' => [
            'name_en' => 'Finance & business',
            'name_ar' => 'المالية وإدارة الأعمال',
        ],
        'growth' => [
            'name_en' => 'Growth',
            'name_ar' => 'النمو والتوسع',
        ],
    ],

    'always_menu_keys' => [
        'dashboard',
        'my_companies',
        'referrals',
        'product_module',
        'employees_management_module',
        'setting',
    ],

    'custom_plan' => [
        'name' => 'custom_build',
        'name_ar' => 'باقة مخصصة',
        'name_en' => 'Custom build',
        'description' => 'Dynamically configured subscription from the plan builder.',
        'description_ar' => 'اشتراك مخصص من منشئ الباقات.',
    ],

    /**
     * Guided recommendations ("What type of system do you have?").
     * Manageable from admin; seeded into entitlement_settings.recommendations.
     */
    'recommendations' => [
        [
            'key' => 'restaurant',
            'name_en' => 'Restaurant / Café',
            'name_ar' => 'مطعم أو مقهى',
            'description_en' => 'POS, waiter & kitchen apps, e-menu, screens, and inventory.',
            'description_ar' => 'كاشير وتطبيق ويتر ومطبخ ومنيو إلكتروني وشاشات ومخزون.',
            'modules' => [
                'cashier_pos',
                'waiter_app',
                'kitchen_app',
                'electronic_menu',
                'digital_screens',
                'inventory',
                'reports',
            ],
            'quotas' => [
                'employees' => 10,
                'establishments' => 1,
                'screen_devices' => 3,
                'pos_devices' => 2,
                'waiter_seats' => 3,
                'kitchen_devices' => 2,
            ],
            'sort_order' => 10,
            'active' => true,
        ],
        [
            'key' => 'retail',
            'name_en' => 'Retail / E‑commerce',
            'name_ar' => 'تجارة وتجزئة',
            'description_en' => 'Finance package, inventory, and reports.',
            'description_ar' => 'باقة المالية والمخزون والتقارير.',
            'modules' => ['finance_business', 'inventory', 'reports'],
            'quotas' => [
                'employees' => 8,
                'establishments' => 2,
            ],
            'sort_order' => 20,
            'active' => true,
        ],
        [
            'key' => 'factory',
            'name_en' => 'Factory / Production',
            'name_ar' => 'مصنع / إنتاج',
            'description_en' => 'Finance, inventory, manufacturing, employee schedule, and reports.',
            'description_ar' => 'المالية والمخزون والتصنيع ودوامات الموظفين والتقارير.',
            'modules' => [
                'finance_business',
                'inventory',
                'manufacturing',
                'employee_schedule',
                'reports',
            ],
            'quotas' => [
                'employees' => 25,
                'establishments' => 2,
            ],
            'sort_order' => 30,
            'active' => true,
        ],
        [
            'key' => 'workshop',
            'name_en' => 'Workshop / Light industry',
            'name_ar' => 'ورشة / صناعة خفيفة',
            'description_en' => 'Finance, inventory, manufacturing, optional POS, and schedule.',
            'description_ar' => 'المالية والمخزون والتصنيع مع كاشير اختياري والدوامات.',
            'modules' => [
                'finance_business',
                'inventory',
                'manufacturing',
                'cashier_pos',
                'employee_schedule',
                'reports',
            ],
            'quotas' => [
                'employees' => 15,
                'establishments' => 1,
                'pos_devices' => 1,
            ],
            'sort_order' => 40,
            'active' => true,
        ],
        [
            'key' => 'services',
            'name_en' => 'Services / Contractors',
            'name_ar' => 'خدمات ومقاولات',
            'description_en' => 'Finance package, employee shifts, and reports.',
            'description_ar' => 'باقة المالية ودوامات الموظفين والتقارير.',
            'modules' => ['finance_business', 'employee_schedule', 'reports'],
            'quotas' => [
                'employees' => 12,
                'establishments' => 1,
            ],
            'sort_order' => 50,
            'active' => true,
        ],
        [
            'key' => 'screens',
            'name_en' => 'Digital screens only',
            'name_ar' => 'شاشات رقمية فقط',
            'description_en' => 'Branch displays and promo players — light start.',
            'description_ar' => 'شاشات الفروع والإعلانات — بداية خفيفة.',
            'modules' => ['digital_screens'],
            'quotas' => [
                'employees' => 3,
                'establishments' => 1,
                'screen_devices' => 5,
            ],
            'sort_order' => 60,
            'active' => true,
        ],
        [
            'key' => 'full',
            'name_en' => 'Full operations',
            'name_ar' => 'تشغيل متكامل',
            'description_en' => 'Front-of-house apps + finance + inventory + manufacturing.',
            'description_ar' => 'تطبيقات الواجهة مع المالية والمخزون والتصنيع.',
            'modules' => [
                'cashier_pos',
                'waiter_app',
                'kitchen_app',
                'electronic_menu',
                'digital_screens',
                'inventory',
                'manufacturing',
                'employee_schedule',
                'finance_business',
                'reports',
            ],
            'quotas' => [
                'employees' => 25,
                'establishments' => 3,
                'screen_devices' => 6,
                'pos_devices' => 3,
                'waiter_seats' => 5,
                'kitchen_devices' => 3,
            ],
            'sort_order' => 70,
            'active' => true,
        ],
    ],
];
