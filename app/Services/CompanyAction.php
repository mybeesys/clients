<?php

namespace App\Services;

use App\Jobs\SeedTenantDatabase;
use App\Models\Company;
use App\Models\Country;
use App\Models\Tenant;
use App\Models\User;
use Stancl\Tenancy\Database\Models\Domain;
use Stancl\Tenancy\Jobs\MigrateDatabase;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;


class CompanyAction
{

    public function __construct(protected User $user)
    {
    }

    public static function getCompanyForm($register)
    {
        return [
            Section::make()
                ->columnSpan(1)
                ->schema([
                    TextInput::make('companyName')
                        ->label(__('fields.name'))
                        ->string()
                        ->unique('companies', 'name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('companyPhone')
                        ->label(__('fields.phone'))
                        ->tel()->minLength(8)->maxLength(11),
                    TextInput::make('website')
                        ->label(__('fields.website'))
                        ->url()
                        ->maxLength(255),
                    TextInput::make('ceo_name')
                        ->label(__('fields.ceo_name'))
                        ->maxLength(255),
                    TextInput::make('tax_name')
                        ->label(__('fields.tax_name'))
                        ->maxLength(255),
                    TextInput::make('tax_number')
                        ->numeric()
                        ->label(__('fields.tax_number'))
                        ->length(13),
                    Select::make('user_id')
                        ->label(__('fields.user'))
                        ->relationship('user', 'email', fn($query) => $query->doesntHave('company'))
                        ->exists('users', 'id')
                        ->searchable()
                        ->preload()
                        ->required()->visible(!$register)

                ]),
            Section::make()
                ->columnSpan(1)
                ->schema([
                    Select::make('country_id')
                        ->label(__('fields.country'))
                        ->options(Country::pluck('name_en', 'id'))->exists('countries', 'id')
                        ->live()->preload()->searchable()->required(),
                    TextInput::make('state')
                        ->label(__('fields.state'))
                        ->string()
                        ->required()
                        ->maxLength(255),
                    TextInput::make('city')
                        ->label(__('fields.city'))
                        ->string()
                        ->required()
                        ->maxLength(255),

                    TextInput::make('national_address')
                        ->string()
                        ->label(__('fields.national_address')),
                    TextInput::make('zipcode')
                        ->numeric()
                        ->label(__('fields.zip_code'))
                        ->required(),
                ]),
            Section::make()
                ->columns(2)
                ->schema([
                    Textarea::make('description')
                        ->label(__('fields.description')),
                    FileUpload::make('logo')
                        ->label(__('fields.logo'))
                        ->image()
                        ->directory('companies/logos'),
                ])->visible(!$register)

            // [
            //     TextInput::make('companyName')
            //         ->label(__('fields.name'))
            //         ->string()
            //         ->unique('companies', 'name')
            //         ->required()
            //         ->maxLength(255),
            //     TextInput::make('companyPhone')
            //         ->label(__('fields.phone'))
            //         ->tel()->minLength(8)->maxLength(11),
            //     TextInput::make('website')
            //         ->label(__('fields.website'))
            //         ->url()
            //         ->suffixIcon('heroicon-m-globe-alt')
            //         ->maxLength(255),
            //     TextInput::make('ceo_name')
            //         ->label(__('fields.ceo_name'))
            //         ->maxLength(255),
            //     TextInput::make('tax_name')
            //         ->label(__('fields.tax_name'))
            //         ->maxLength(255),
            //     Select::make('country_id')
            //         ->label(__('fields.country'))
            //         ->options(Country::pluck('name_en', 'id'))->exists('countries', 'id')
            //         ->live()->preload()->searchable()->required(),
            //     TextInput::make('state')
            //         ->label(__('fields.state'))
            //         ->string()
            //         ->required()
            //         ->maxLength(255),
            //     TextInput::make('city')
            //         ->label(__('fields.city'))
            //         ->string()
            //         ->required()
            //         ->maxLength(255),
            //     TextInput::make('national_address')
            //         ->string()
            //         ->label(__('fields.national_address')),
            //     TextInput::make('zipcode')
            //         ->numeric()
            //         ->label(__('fields.zip_code'))
            //         ->required(),
            // ]


        ];
    }

    public function storeCompany($data)
    {
        $tenant = null;
        $company = null;
        try {
            $company = Company::create([
                'name' => $data['companyName'],
                'user_id' => $this->user->id,
                'phone' => $data['companyPhone'],
                'website' => $data['website'],
                'ceo_name' => $data['ceo_name'],
                'tax_name' => $data['tax_name'],
                'tax_number' => $data['tax_number'],
                'country_id' => $data['country_id'],
                'state' => $data['state'],
                'city' => $data['city'],
                'national_address' => $data['national_address'],
                'zip_code' => $data['zipcode'],
                'description' => $data['description'] ?? null,
                'logo' => $data['logo'] ?? null
            ]);

            $tenant = Tenant::create([
                'id' => trim($data['companyName']),
                'company_id' => $company->id,
                'user_id' => $this->user->id
            ]);


            Domain::create([
                'domain' => trim($data['companyName']) . '.' . str_replace(['http://', 'https://'], '', config('app.url')),
                'tenant_id' => trim($data['companyName'])
            ]);

            MigrateDatabase::withChain([
                new SeedTenantDatabase($tenant)
            ])->dispatch($tenant);

            return $company;
        } catch (\Throwable $e) {
            $this->cleanup($tenant, $company);
            throw $e;
        }
    }

    private function cleanup(?Tenant $tenant, ?Company $company)
    {
        if ($tenant) {
            try {
                $tenant->domains()->delete();
                $tenant->delete();
            } catch (\Exception $e) {
                \Log::error('Tenant cleanup error: ' . $e->getMessage());
            }
        }

        if ($company) {
            try {
                $company->forceDelete();
            } catch (\Exception $e) {
                \Log::error('Company cleanup error: ' . $e->getMessage());
            }
        }

        try {
            $this->user->forceDelete();
        } catch (\Exception $e) {
            \Log::error('User cleanup error: ' . $e->getMessage());
        }
    }

}