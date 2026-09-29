<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Country;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Entitlements\EntitlementProvisioner;
use App\Services\ReferralService;
use App\Support\TenantKeyGenerator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class RegistrationService
{
    public function register(array $data): User
    {
        Validator::make($data, [
            'userName' => ['required', 'string', 'min:2', 'max:255', Rule::unique('users', 'name')],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone_number' => ['required', 'string', 'regex:/^\+9665[0-9]{8}$/'],
            'password' => ['required', 'string', 'max:255', 'confirmed'],
        ], [
            'phone_number.regex' => __('main.wizard.saudi_phone_invalid'),
        ], [
            'userName' => __('fields.name'),
            'email' => __('fields.email'),
            'phone_number' => __('fields.phone_number'),
            'password' => __('fields.password'),
        ])->validate();

        $payload = $this->mapCompanyPayload($data);

        $user = User::create([
            'name' => $data['userName'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_company' => true,
            'email_verified_at' => now(),
        ]);

        try {
            $company = (new CompanyAction($user))->storeCompany($payload);

            $this->createSubscription($company, $data);

            app(ReferralService::class)->handleRegistrationConversion(
                referralCodeValue: $data['referral_code'] ?? session('referral_code'),
                company: $company,
                subscriber: $user,
                planId: $company->fresh()?->subscription?->plan_id,
                request: request(),
            );

            return $user;
        } catch (Throwable $e) {
            $this->rollback($user);

            throw $e;
        }
    }

    protected function mapCompanyPayload(array $data): array
    {
        $company = $data['company'] ?? [];

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
            'phone' => $company['phone'] ?? ($data['phone_number'] ?? null),
            'website' => $company['website'] ?? null,
            'ceo_name' => $company['ceo_name'] ?? null,
            'tax_name' => $company['tax_name'] ?? null,
            'logo' => $company['logo'] ?? null,
            'owner_phone_number' => $data['phone_number'] ?? null,
        ];
    }

    protected function createSubscription(Company $company, array $data): void
    {
        $planConfig = $data['plan_config'] ?? null;

        if (is_array($planConfig) && ! empty($planConfig['modules'])) {
            app(EntitlementProvisioner::class)->provision($company, $planConfig);

            return;
        }

        $subscription = $data['subscription'] ?? null;

        if (empty($subscription['plan_id'])) {
            return;
        }

        Subscription::create([
            'plan_id' => $subscription['plan_id'],
            'started_at' => $subscription['started_at'] ?? now(),
            'expired_at' => $subscription['expired_at'] ?? null,
            'grace_days_ended_at' => $subscription['grace_days_ended_at'] ?? null,
            'subscriber_type' => Company::class,
            'subscriber_id' => $company->id,
        ]);
    }

    protected function rollback(User $user): void
    {
        $company = Company::query()->where('user_id', $user->id)->first();

        if ($company) {
            try {
                $company->tenant?->delete();
            } catch (Throwable) {
            }

            $company->forceDelete();
        }

        $user->forceDelete();
    }
}
