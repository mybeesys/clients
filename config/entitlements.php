<?php

/**
 * Commercial entitlement catalog for custom subscription builds.
 *
 * IMPORTANT: These keys gate UI/API surfaces only. Never disable nwidart Modules
 * via modules_statuses.json — hard class coupling would crash dependents.
 *
 * Platform core (always granted): product catalog, employees, establishments,
 * general settings, and the accounting ledger engine (posting stays available
 * for sold modules that need it; full Accounting UI is sold separately).
 */
return [

    'currency' => 'SAR',

    /** Annual billing charges a full year (12× monthly). Use coupons for discounts. */
    'yearly_months_charged' => 12,

    'platform' => [
        'key' => 'platform',
        'name_en' => 'Core platform',
        'name_ar' => 'المنصة الأساسية',
        'description_en' => 'Products, employees, branches, settings, and ledger engine.',
        'description_ar' => 'المنتجات والموظفين والفروع والإعدادات ومحرك القيود المحاسبية.',
        'price_month' => 199,
        'always_included' => true,
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
    ],

    /**
     * Sellable commercial modules.
     * requires: other module keys auto-selected when this one is chosen.
     * soft_requires: informational only (already covered by platform core).
     */
    'modules' => [

        'cashier_pos' => [
            'key' => 'cashier_pos',
            'group' => 'front_of_house',
            'name_en' => 'Cashier & POS',
            'name_ar' => 'الكاشير ونقاط البيع',
            'description_en' => 'POS orders, kitchen display, waiter apps, and tables.',
            'description_ar' => 'طلبات الكاشير وشاشة المطبخ وتطبيقات الويتر والطاولات.',
            'price_month' => 349,
            'icon' => 'cash-register',
            'requires' => [],
            'menu_keys' => ['pos_roles', 'tables', 'areas'],
            'api_prefixes' => [
                'api/new-order',
                'api/kitchen-orders',
                'api/tables',
                'api/waiter',
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
            'menu_keys' => ['tables_qr', 'menu_qr', 'menu_feedback', 'tables', 'areas'],
            'api_prefixes' => [
                'api/order',
            ],
        ],

        'digital_screens' => [
            'key' => 'digital_screens',
            'group' => 'front_of_house',
            'name_en' => 'Digital screens',
            'name_ar' => 'الشاشات الرقمية',
            'description_en' => 'Branch display devices, playlists, and promo players.',
            'description_ar' => 'شاشات الفروع وقوائم التشغيل والإعلانات.',
            'price_month' => 149,
            'icon' => 'tv',
            'requires' => [],
            'menu_keys' => ['screen_module'],
            'api_prefixes' => [
                'api/v1/screen',
            ],
        ],

        'inventory' => [
            'key' => 'inventory',
            'group' => 'operations',
            'name_en' => 'Inventory & manufacturing',
            'name_ar' => 'المخزون والتصنيع',
            'description_en' => 'Stock, transfers, waste, and prep / light manufacturing.',
            'description_ar' => 'المخزون والتحويل والهدر والتحضير والتصنيع الخفيف.',
            'price_month' => 249,
            'icon' => 'warehouse',
            'requires' => [],
            'menu_keys' => ['inventory_module'],
            'api_prefixes' => [],
        ],

        'sales' => [
            'key' => 'sales',
            'group' => 'finance',
            'name_en' => 'Sales',
            'name_ar' => 'المبيعات',
            'description_en' => 'Quotations, sell invoices, returns, customers, and coupons.',
            'description_ar' => 'عروض الأسعار وفواتير البيع والمرتجعات والعملاء والكوبونات.',
            'price_month' => 199,
            'icon' => 'receipt',
            'requires' => [],
            'menu_keys' => ['sales', 'clients_suppliers_module'],
            'api_prefixes' => [
                'api/v1/invoices',
                'api/v1/coupons',
            ],
        ],

        'purchases' => [
            'key' => 'purchases',
            'group' => 'finance',
            'name_en' => 'Purchases',
            'name_ar' => 'المشتريات',
            'description_en' => 'Purchase orders, invoices, returns, and supplier vouchers.',
            'description_ar' => 'أوامر الشراء والفواتير والمرتجعات وسندات الموردين.',
            'price_month' => 149,
            'icon' => 'cart',
            'requires' => [],
            'menu_keys' => ['purchases', 'clients_suppliers_module'],
            'api_prefixes' => [],
        ],

        'accounting' => [
            'key' => 'accounting',
            'group' => 'finance',
            'name_en' => 'Accounting',
            'name_ar' => 'المحاسبة',
            'description_en' => 'Chart of accounts, journals, vouchers, cost centers, and reports.',
            'description_ar' => 'شجرة الحسابات والقيود والسندات ومراكز التكلفة والتقارير.',
            'price_month' => 249,
            'icon' => 'calculator',
            'requires' => [],
            'menu_keys' => ['accounting_module'],
            'api_prefixes' => [],
        ],

        'expenses' => [
            'key' => 'expenses',
            'group' => 'finance',
            'name_en' => 'Expenses',
            'name_ar' => 'المصروفات',
            'description_en' => 'Expense management workspace (requires Accounting).',
            'description_ar' => 'إدارة المصروفات (تتطلب وحدة المحاسبة).',
            'price_month' => 79,
            'icon' => 'wallet',
            'requires' => ['accounting'],
            'menu_keys' => ['expenses_manage'],
            'api_prefixes' => [],
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
            'menu_keys' => ['franchise'],
            'api_prefixes' => [],
        ],

        'reports' => [
            'key' => 'reports',
            'group' => 'growth',
            'name_en' => 'Advanced reports',
            'name_ar' => 'التقارير المتقدمة',
            'description_en' => 'Unlocks operational reports for the modules you buy (sales, purchases, inventory, POS…). Not useful alone.',
            'description_ar' => 'يفعّل التقارير التشغيلية للوحدات التي تشتريها (مبيعات، مشتريات، مخزون، كاشير…). لا تعمل وحدها بدون وحدات بيانات.',
            'price_month' => 99,
            'icon' => 'chart',
            'requires' => [],
            /** At least one of these must be selected with reports. */
            'requires_any' => ['sales', 'purchases', 'inventory', 'cashier_pos', 'accounting'],
            'menu_keys' => ['reports_module'],
            'api_prefixes' => [],
        ],
    ],

    'groups' => [
        'front_of_house' => [
            'name_en' => 'Front of house',
            'name_ar' => 'واجهة المطعم والعملاء',
        ],
        'operations' => [
            'name_en' => 'Operations',
            'name_ar' => 'التشغيل',
        ],
        'finance' => [
            'name_en' => 'Finance & ERP',
            'name_ar' => 'المالية وإدارة الأعمال',
        ],
        'growth' => [
            'name_en' => 'Growth',
            'name_ar' => 'النمو والتوسع',
        ],
    ],

    /**
     * Menu item names in my-bee-company/config/menu.php that are always visible
     * (subject to Spatie permissions) for every subscribed tenant.
     */
    'always_menu_keys' => [
        'dashboard',
        'my_companies',
        'referrals',
        'product_module',
        'employees_management_module',
        'setting',
    ],

    /** Custom subscription plan slug used when provisioning a build. */
    'custom_plan' => [
        'name' => 'custom_build',
        'name_ar' => 'باقة مخصصة',
        'name_en' => 'Custom build',
        'description' => 'Dynamically configured subscription from the plan builder.',
        'description_ar' => 'اشتراك مخصص من منشئ الباقات.',
    ],

    /**
     * Guided recommendations shown when opening the plan builder.
     * Applying a recommendation selects modules (+ optional quota defaults).
     */
    'recommendations' => [
        [
            'key' => 'restaurant',
            'name_en' => 'Restaurant / Café',
            'name_ar' => 'مطعم أو مقهى',
            'description_en' => 'POS, electronic menu, kitchen flow, screens, and stock.',
            'description_ar' => 'كاشير ومنيو إلكتروني وتدفق المطبخ والشاشات والمخزون.',
            'modules' => ['cashier_pos', 'electronic_menu', 'digital_screens', 'inventory', 'reports'],
            'quotas' => [
                'employees' => 10,
                'establishments' => 1,
                'screen_devices' => 3,
            ],
        ],
        [
            'key' => 'retail',
            'name_en' => 'Retail / E‑commerce',
            'name_ar' => 'تجارة وتجزئة',
            'description_en' => 'Sales, purchases, inventory, accounting, and reports.',
            'description_ar' => 'مبيعات ومشتريات ومخزون ومحاسبة وتقارير.',
            'modules' => ['sales', 'purchases', 'inventory', 'accounting', 'reports'],
            'quotas' => [
                'employees' => 8,
                'establishments' => 2,
            ],
        ],
        [
            'key' => 'services',
            'name_en' => 'Services / Contractors',
            'name_ar' => 'خدمات ومقاولات',
            'description_en' => 'Invoicing, purchases, full accounting, expenses, and reports.',
            'description_ar' => 'فواتير ومشتريات ومحاسبة كاملة ومصروفات وتقارير.',
            'modules' => ['sales', 'purchases', 'accounting', 'expenses', 'reports'],
            'quotas' => [
                'employees' => 12,
                'establishments' => 1,
            ],
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
        ],
        [
            'key' => 'full',
            'name_en' => 'Full operations',
            'name_ar' => 'تشغيل متكامل',
            'description_en' => 'Front of house + ERP modules for growing companies.',
            'description_ar' => 'واجهة العملاء مع وحدات الأعمال للشركات النامية.',
            'modules' => [
                'cashier_pos',
                'electronic_menu',
                'digital_screens',
                'inventory',
                'sales',
                'purchases',
                'accounting',
                'expenses',
                'reports',
            ],
            'quotas' => [
                'employees' => 20,
                'establishments' => 3,
                'screen_devices' => 6,
            ],
        ],
    ],
];
