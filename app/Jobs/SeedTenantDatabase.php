<?php

namespace App\Jobs;

use App\Models\Tenant;
use DB;
use Hash;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class SeedTenantDatabase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected Tenant $tenant)
    {
        $this->tenant = $tenant;
    }


    public function handle(): void
    {
        try {
            $this->tenant->run(function () {
                $this->insertDefaultEstablishment();
                $this->insertPermissions();
                $this->insertEmployee();
                $this->insertCountries();
            });
        } catch (\Exception $e) {
            \Log::error('Seeding failed: ' . $e->getMessage());
            \Log::error('Trace: ' . $e->getTraceAsString());

            throw $e;
        }
    }

    private function insertEmployee()
    {
        try {
            $default_est_id = DB::table('est_establishments')->whereNotNull('parent_id')->first()?->id;
            DB::table('emp_employees')->updateOrInsert([
                'email' => 'admin@admin.com'
            ], [
                'name' => 'آدمن',
                'name_en' => 'admin',
                'establishment_id' => $default_est_id,
                'password' => Hash::make('12345678'),
                'pin' => 99913,
                'ems_access' => true,
                'pos_is_active' => true
            ]);
            $employee_id = DB::table('emp_employees')->where('email', 'admin@admin.com')->first()?->id;
            $permissions = DB::table('permissions')->where('name', 'LIKE', '%all%')->where('type', 'ems')->pluck('id');
            $permissions->each(function ($permission_id) use ($employee_id) {
                DB::table('model_has_permissions')->insertOrIgnore([
                    'permission_id' => $permission_id,
                    'model_type' => 'Modules\Employee\Models\Employee',
                    'model_id' => $employee_id
                ]);
            });
        } catch (\Exception $e) {
            \Log::error('Employee insertion failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function insertPermissions()
    {
        try {
            $pos_permissions = include base_path('../mybeeCompany/Modules/Employee/data/pos-permissions.php');
            $dashboard_permissions = include base_path('../mybeeCompany/Modules/Employee/data/dashboard-permissions.php');
            $permissions = array_merge($pos_permissions, $dashboard_permissions);

            foreach ($permissions as $permission) {
                DB::table('permissions')->updateOrInsert([
                    'name' => $permission['name']
                ], [
                    'type' => $permission['type'],
                    'name_ar' => $permission['name_ar'],
                    'description' => $permission['description'],
                    'description_ar' => $permission['description_ar'],
                    'guard_name' => 'web'
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Permissions insertion failed: ' . $e->getMessage());
            throw $e; // Or handle as needed
        }
    }

    private function insertDefaultEstablishment()
    {
        DB::table('est_establishments')->updateOrInsert(
            ['name_en' => 'main'],
            [
                'name' => 'رئيسي',
                'is_main' => true,
            ]
        );
        $id = DB::table('est_establishments')
            ->where('name_en', 'main')->whereNull('parent_id')
            ->value('id');
        if ($id) {
            DB::table('est_establishments')->updateOrInsert([
                'parent_id' => $id
            ], [
                'name' => 'رئيسي',
                'name_en' => 'main',
                'is_main' => false,
            ]);
        }
    }

    private function insertCountries()
    {
        try {
            $filePath = base_path('/data/countries.json');
            $countriesData = json_decode(file_get_contents($filePath), true);

            foreach ($countriesData as $country) {
                $nameEn = $country['name']['common'] ?? null;
                $nameAr = $country['translations']['ara']['common'] ?? null;
                $isoCode = $country['cca2'] ?? null;
                $dialCode = isset($country['idd']['root']) && isset($country['idd']['suffixes'])
                    ? $country['idd']['root'] . $country['idd']['suffixes'][0]
                    : '+000';
                $currencyData = $country['currencies'] ?? [];
                $currencyNameEn = $currencySymbolEn = $currencyNameAr = $currencySymbolAr = null;

                if (!empty($currencyData)) {
                    $firstCurrency = array_values($currencyData)[0];
                    $currencyNameEn = $firstCurrency['name'] ?? null;
                    $currencySymbolEn = $firstCurrency['symbol'] ?? null;
                    $currencyNameAr = $firstCurrency['translations']['ara']['name'] ?? null;
                    $currencySymbolAr = $firstCurrency['translations']['ara']['symbol'] ?? null;
                }
                DB::table('countries')->insert([
                    [
                        'iso_code' => $isoCode,
                        'name_en' => $nameEn,
                        'name_ar' => $nameAr,
                        'dial_code' => $dialCode,
                        'currency_name_en' => $currencyNameEn,
                        'currency_symbol_en' => $currencySymbolEn,
                        'currency_name_ar' => $currencyNameAr,
                        'currency_symbol_ar' => $currencySymbolAr,
                    ]
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Countries insertion failed: ' . $e->getMessage());
            throw $e; // Or handle as needed
        }
    }
}
