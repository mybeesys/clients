<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Country;
use App\Models\User;
use Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // $this->call(CountryStateCityTableSeeder::class);

        $filePath = base_path('/data/countries.json');

        if (file_exists($filePath)) {
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

                Country::updateOrCreate(
                    ['iso_code' => $isoCode],
                    [
                        'name_en' => $nameEn,
                        'name_ar' => $nameAr,
                        'dial_code' => $dialCode,
                        'currency_name_en' => $currencyNameEn,
                        'currency_symbol_en' => $currencySymbolEn,
                        'currency_name_ar' => $currencyNameAr,
                        'currency_symbol_ar' => $currencySymbolAr,
                    ]
                );
            }

            $this->command->info('Countries data imported successfully!');
        } else {
            $this->command->error('Failed to fetch countries data');
        }

        $user = User::updateOrCreate(['email' => 'admin@admin.com'], [
            'name' => 'Admin',
            'password' => Hash::make('admin123456'),
            'is_company' => false
        ]);

        Company::updateOrCreate(['user_id' => $user->id, 'name' => 'admin'], [
            'subscribed' => 1,
            'description' => 'admin',
            'ceo_name' => 'admin',
        ]);
    }
}
