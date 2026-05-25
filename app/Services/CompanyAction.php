<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Country;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantKeyGenerator;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;
use Stancl\Tenancy\Database\Models\Domain;

class CompanyAction
{
    public function __construct(protected User $user) {}

    public static function businessTypeOptions(): array
    {
        return [
            'contractors' => __('fields.business_types.contractors'),
            'e-commerce' => __('fields.business_types.e-commerce'),
            'restaurant-cafe' => __('fields.business_types.restaurant-cafe'),
            'services' => __('fields.business_types.services'),
            'general' => __('fields.business_types.general'),
        ];
    }

    protected static function fieldName(string $name, string $prefix = ''): string
    {
        return $prefix !== '' ? "{$prefix}.{$name}" : $name;
    }

    /**
     * Minimal company fields for the admin onboarding wizard (step: company).
     *
     * @return array<int, Section>
     */
    public static function getCompanyWizardSchema(string $prefix = 'company'): array
    {
        $f = fn (string $name) => static::fieldName($name, $prefix);

        return [
            Section::make(__('fields.main_info'))
                ->columns(2)
                ->schema([
                    TextInput::make($f('name_ar'))
                        ->label(__('fields.name_ar'))
                        ->required()
                        ->unique('companies', 'name')
                        ->maxLength(255),
                    TextInput::make($f('name_en'))
                        ->label(__('fields.name_en'))
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->helperText(__('main.wizard.name_en_domain_hint'))
                        ->afterStateUpdated(fn (Set $set, ?string $state) => $set(
                            $f('tenant_key'),
                            TenantKeyGenerator::fromEnglishName($state)
                        )),
                    Hidden::make($f('tenant_key'))->dehydrated(),
                    Select::make($f('business_type'))
                        ->label(__('fields.business_type'))
                        ->options(static::businessTypeOptions())
                        ->default('general')
                        ->required(),
                    TextInput::make($f('tax_number'))
                        ->numeric()
                        ->label(__('fields.tax_number'))
                        ->required()
                        ->maxLength(15),
                    TextInput::make($f('national_address'))
                        ->label(__('fields.national_address'))
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Textarea::make($f('description'))
                        ->label(__('fields.description'))
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ];
    }

    public static function getCompanyFormSections(string $prefix = '', bool $forRegistration = false): array
    {
        $f = fn (string $name) => static::fieldName($name, $prefix);

        $mainFields = [
            TextInput::make($f('name'))
                ->label(__('fields.name_ar'))
                ->string()
                ->unique('companies', 'name', ignoreRecord: true)
                ->required()
                ->maxLength(255),
            Select::make($f('business_type'))
                ->label(__('fields.business_type'))
                ->options(static::businessTypeOptions())
                ->default('general')
                ->required(),
            TextInput::make($f('phone'))
                ->label(__('fields.phone'))
                ->tel()
                ->minLength(8)
                ->maxLength(20),
            TextInput::make($f('website'))
                ->label(__('fields.website'))
                ->url()
                ->maxLength(255),
            TextInput::make($f('ceo_name'))
                ->label(__('fields.ceo_name'))
                ->maxLength(255),
            TextInput::make($f('tax_name'))
                ->label(__('fields.tax_name'))
                ->maxLength(255),
            TextInput::make($f('tax_number'))
                ->numeric()
                ->label(__('fields.tax_number'))
                ->maxLength(15),
        ];

        if (! $forRegistration) {
            $mainFields[] = Select::make($f('user_id'))
                ->label(__('fields.user'))
                ->relationship(
                    'user',
                    'email',
                    modifyQueryUsing: function (Builder $query, $livewire): void {
                        $ownerId = $livewire->record?->user_id ?? null;

                        $query->where(function (Builder $inner) use ($ownerId): void {
                            $inner->doesntHave('company');

                            if ($ownerId) {
                                $inner->orWhere('users.id', $ownerId);
                            }
                        });
                    }
                )
                ->exists('users', 'id')
                ->searchable()
                ->preload()
                ->required();
        }

        return [
            Section::make(__('fields.main_info'))
                ->columns(2)
                ->schema($mainFields),
            Section::make(__('fields.address'))
                ->columns(2)
                ->schema([
                    Select::make($f('country_id'))
                        ->label(__('fields.country'))
                        ->options(Country::pluck('name_en', 'id'))
                        ->exists('countries', 'id')
                        ->live()
                        ->preload()
                        ->searchable()
                        ->required(),
                    TextInput::make($f('state'))
                        ->label(__('fields.state'))
                        ->string()
                        ->required()
                        ->maxLength(255),
                    TextInput::make($f('city'))
                        ->label(__('fields.city'))
                        ->string()
                        ->required()
                        ->maxLength(255),
                    TextInput::make($f('national_address'))
                        ->string()
                        ->label(__('fields.national_address'))
                        ->maxLength(255),
                    TextInput::make($f('zipcode'))
                        ->label(__('fields.zip_code'))
                        ->required()
                        ->maxLength(20),
                ]),
            Section::make(__('fields.additional_details'))
                ->schema([
                    Textarea::make($f('description'))
                        ->label(__('fields.description'))
                        ->rows(3),
                    FileUpload::make($f('logo'))
                        ->label(__('fields.logo'))
                        ->image()
                        ->directory('companies/logos'),
                ]),
        ];
    }

    public static function getCompanyForm(bool $forRegistration = false): array
    {
        return static::getCompanyFormSections('', $forRegistration);
    }

    public function storeCompany($data)
    {
        $tenant = null;
        $company = null;

        $tenantKey = $data['tenant_key']
            ?? TenantKeyGenerator::fromEnglishName($data['name_en'] ?? $data['name'] ?? null);

        $company = Company::create([
            'name' => $data['name'],
            'business_type' => $data['business_type'],
            'user_id' => $this->user->id,
            'phone' => $data['phone'] ?? null,
            'website' => $data['website'] ?? null,
            'ceo_name' => $data['ceo_name'] ?? null,
            'tax_name' => $data['tax_name'] ?? null,
            'tax_number' => $data['tax_number'] ?? null,
            'country_id' => $data['country_id'],
            'state' => $data['state'],
            'city' => $data['city'],
            'national_address' => $data['national_address'] ?? null,
            'zipcode' => $data['zipcode'],
            'description' => $data['description'] ?? null,
            'logo' => $data['logo'] ?? null,
        ]);

        $tenant = Tenant::create([
            'id' => $tenantKey,
            'company_id' => $company->id,
            'user_id' => $this->user->id,
            'owner_phone_number' => $data['owner_phone_number'] ?? null,
        ]);

        Domain::create([
            'domain' => $tenantKey.'.'.str_replace(['http://', 'https://'], '', config('app.url')),
            'tenant_id' => $tenantKey,
        ]);

        return $company;
    }

    private function cleanup(?Tenant $tenant, ?Company $company)
    {
        if ($tenant) {
            try {
                $tenant->domains()->delete();
                $tenant->delete();
            } catch (\Exception $e) {
                \Log::error('Tenant cleanup error: '.$e->getMessage());
            }
        }

        if ($company) {
            try {
                $company->forceDelete();
            } catch (\Exception $e) {
                \Log::error('Company cleanup error: '.$e->getMessage());
            }
        }

        try {
            $this->user->forceDelete();
        } catch (\Exception $e) {
            \Log::error('User cleanup error: '.$e->getMessage());
        }
    }
}
