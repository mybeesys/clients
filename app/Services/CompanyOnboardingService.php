<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Country;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantKeyGenerator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class CompanyOnboardingService
{
    public function create(array $data): Company
    {
        $payload = $this->mapCompanyPayload($data);
        $user = $this->createUser($data);

        try {
            $company = (new CompanyAction($user))->storeCompany($payload);

            $this->createSubscription($company, $data);

            return $company;
        } catch (Throwable $e) {
            $this->rollbackOnboarding($user);

            throw $e;
        }
    }

    protected function rollbackOnboarding(User $user): void
    {
        $company = Company::query()->where('user_id', $user->id)->first();

        if ($company) {
            try {
                $company->tenant?->delete();
            } catch (Throwable) {
                // Tenant DB may be missing if creation failed early.
            }

            $company->forceDelete();
        }

        $user->forceDelete();
    }

    protected function createUser(array $data): User
    {
        Validator::make(
            ['user' => $data['user'] ?? []],
            [
                'user.name' => ['required', 'string', 'min:2', 'max:255', Rule::unique('users', 'name')],
                'user.email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
                'user.password' => ['required', 'string', 'max:255'],
            ],
            [],
            [
                'user.name' => __('fields.name'),
                'user.email' => __('fields.email'),
                'user.password' => __('fields.password'),
            ]
        )->validate();

        $user = User::create([
            'name' => $data['user']['name'],
            'email' => $data['user']['email'],
            'password' => $data['user']['password'],
            'is_company' => $data['user']['is_company'] ?? true,
            'email_verified_at' => $data['user']['email_verified_at'] ?? now(),
        ]);

        if (! empty($data['user']['roles'])) {
            $user->syncRoles($data['user']['roles']);
        }

        return $user;
    }

    protected function mapCompanyPayload(array $data): array
    {
        $company = $data['company'];

        $tenantKey = $company['tenant_key']
            ?? TenantKeyGenerator::fromEnglishName($company['name_en'] ?? null);

        if (Tenant::query()->whereKey($tenantKey)->exists()) {
            throw ValidationException::withMessages([
                'company.name_en' => __('main.wizard.tenant_key_taken'),
            ]);
        }

        return [
            'name' => $company['name_ar'],
            'name_en' => $company['name_en'] ?? null,
            'tenant_key' => $tenantKey,
            'city' => $company['city'] ?? '-',
            'tax_number' => $company['tax_number'] ?? null,
            'description' => $company['description'] ?? null,
            'business_type' => $company['business_type'] ?? 'general',
            'country_id' => $company['country_id'] ?? Country::defaultId(),
            'state' => $company['state'] ?? '-',
            'zipcode' => $company['zipcode'] ?? '00000',
            'national_address' => $company['national_address'] ?? null,
            'phone' => $company['phone'] ?? null,
            'website' => $company['website'] ?? null,
            'ceo_name' => $company['ceo_name'] ?? null,
            'tax_name' => $company['tax_name'] ?? null,
            'logo' => $company['logo'] ?? null,
            'owner_phone_number' => $data['user']['phone_number'] ?? null,
        ];
    }

    protected function createSubscription(Company $company, array $data): void
    {
        $subscription = $data['subscription'] ?? null;

        if (empty($subscription['plan_id'])) {
            return;
        }

        Subscription::create([
            'plan_id' => $subscription['plan_id'],
            'started_at' => $subscription['started_at'],
            'expired_at' => $subscription['expired_at'] ?? null,
            'grace_days_ended_at' => $subscription['grace_days_ended_at'] ?? null,
            'subscriber_type' => Company::class,
            'subscriber_id' => $company->id,
        ]);
    }
}
