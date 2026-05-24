<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tenant application path (mybeeCompany)
    |--------------------------------------------------------------------------
    |
    | Central app provisions tenant DBs using migrations and seed data from
    | the sibling tenant Laravel application (mybeeCompany).
    |
    */
    'path' => env('TENANT_APP_PATH', base_path('../mybeeCompany')),

    'migration_paths' => [
        'Modules/Employee/database/migrations/tenant',
        'Modules/Establishment/database/migrations/tenant',
        'Modules/Product/database/migrations/tenant',
        'Modules/Accounting/database/migrations/tenant',
        'Modules/Inventory/database/migrations/tenant',
        'Modules/ClientsAndSuppliers/database/migrations/tenant',
        'Modules/Sales/database/migrations/tenant',
        'Modules/General/database/migrations/tenant',
        'Modules/purchases/database/migrations/tenant',
    ],

    'permission_data_paths' => [
        'Modules/Employee/data/pos-permissions.php',
        'Modules/Employee/data/dashboard-permissions.php',
    ],
];
