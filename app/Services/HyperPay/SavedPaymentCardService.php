<?php

namespace App\Services\HyperPay;

use App\Models\Company;
use App\Models\HyperPayPayment;
use App\Models\SavedPaymentCard;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SavedPaymentCardService
{
    /**
     * Persist a HyperPay registration token from a successful payment response.
     *
     * Same physical card (brand + last4 + expiry) is never stored twice for a company:
     * a newer registration token updates the existing row and sibling duplicates are soft-deleted.
     *
     * @param  array<string, mixed>  $gatewayResponse
     */
    public function storeFromGatewayResponse(
        Company $company,
        array $gatewayResponse,
        ?HyperPayPayment $payment = null,
        ?User $user = null,
    ): ?SavedPaymentCard {
        $registrationId = (string) (
            data_get($gatewayResponse, 'registrationId')
            ?: data_get($gatewayResponse, 'registration.id')
            ?: data_get($gatewayResponse, 'registrations.0.id')
            ?: data_get($payment?->request_payload ?? [], 'registrations[0].id')
            ?: data_get($payment?->request_payload ?? [], 'registrationId')
            ?: ''
        );

        // Standalone registration responses often use `id` as the registration token.
        if ($registrationId === '' && $payment?->source === HyperPayPayment::SOURCE_SAVE_CARD) {
            $registrationId = (string) data_get($gatewayResponse, 'id', '');
        }

        if ($registrationId === '') {
            return null;
        }

        $brand = $this->normalizeBrand(data_get($gatewayResponse, 'paymentBrand'));
        $lastFour = $this->normalizeLastFour(data_get($gatewayResponse, 'card.last4Digits'));
        $expiryMonth = $this->normalizeExpiryMonth(data_get($gatewayResponse, 'card.expiryMonth'));
        $expiryYear = $this->normalizeExpiryYear(data_get($gatewayResponse, 'card.expiryYear'));
        $cardBin = $this->normalizeBin(data_get($gatewayResponse, 'card.bin'));
        $holder = trim((string) data_get($gatewayResponse, 'card.holder', ''));
        $holder = $holder !== '' ? mb_substr($holder, 0, 120) : null;

        return DB::transaction(function () use (
            $company,
            $gatewayResponse,
            $payment,
            $user,
            $registrationId,
            $brand,
            $lastFour,
            $expiryMonth,
            $expiryYear,
            $cardBin,
            $holder,
        ) {
            $keeper = $this->resolveKeeper(
                $company,
                $registrationId,
                $brand,
                $lastFour,
                $expiryMonth,
                $expiryYear,
            );

            // Free the unique (company_id, registration_id) slot if another row holds it.
            if ($keeper) {
                SavedPaymentCard::withTrashed()
                    ->where('company_id', $company->id)
                    ->where('registration_id', $registrationId)
                    ->where('id', '!=', $keeper->id)
                    ->get()
                    ->each(function (SavedPaymentCard $other) {
                        $this->retireDuplicate($other);
                    });
            }

            if ($this->hasFingerprint($brand, $lastFour, $expiryMonth, $expiryYear)) {
                $this->retireFingerprintSiblings(
                    $company,
                    $brand,
                    $lastFour,
                    $expiryMonth,
                    $expiryYear,
                    $keeper?->id,
                );
            }

            SavedPaymentCard::query()
                ->where('company_id', $company->id)
                ->update(['is_default' => false]);

            $attributes = [
                'user_id' => $user?->id ?? $keeper?->user_id,
                'hyperpay_payment_id' => $payment?->id,
                'payment_id' => data_get($gatewayResponse, 'id'),
                'registration_id' => $registrationId,
                'brand' => $brand ?: $keeper?->brand,
                'card_bin' => $cardBin ?: $keeper?->card_bin,
                'last_four' => $lastFour ?: $keeper?->last_four,
                'holder' => $holder ?: $keeper?->holder,
                'expiry_month' => $expiryMonth ?: $keeper?->expiry_month,
                'expiry_year' => $expiryYear ?: $keeper?->expiry_year,
                'nickname' => $keeper?->nickname,
                'is_default' => true,
                'status' => 'active',
                'gateway_payload' => [
                    'registrationId' => $registrationId,
                    'paymentBrand' => $brand ?: data_get($gatewayResponse, 'paymentBrand'),
                    'card' => data_get($gatewayResponse, 'card'),
                    'standingInstruction' => data_get($gatewayResponse, 'standingInstruction'),
                ],
                'deleted_at' => null,
            ];

            if ($keeper) {
                $keeper->forceFill($attributes)->save();

                return $keeper->fresh();
            }

            return SavedPaymentCard::query()->create(array_merge($attributes, [
                'company_id' => $company->id,
            ]));
        });
    }

    /**
     * Soft-delete duplicate active cards that share the same fingerprint per company.
     * Keeps the default card when present, otherwise the newest id.
     */
    public function dedupeForCompany(Company $company): int
    {
        $removed = 0;

        return (int) DB::transaction(function () use ($company, &$removed) {
            $cards = SavedPaymentCard::query()
                ->where('company_id', $company->id)
                ->where('status', 'active')
                ->orderByDesc('is_default')
                ->orderByDesc('id')
                ->get();

            $seen = [];

            foreach ($cards as $card) {
                $brand = $this->normalizeBrand($card->brand);
                $lastFour = $this->normalizeLastFour($card->last_four);
                $expiryMonth = $this->normalizeExpiryMonth($card->expiry_month);
                $expiryYear = $this->normalizeExpiryYear($card->expiry_year);

                if (! $this->hasFingerprint($brand, $lastFour, $expiryMonth, $expiryYear)) {
                    continue;
                }

                $key = $this->fingerprintKey($brand, $lastFour, $expiryMonth, $expiryYear);

                if (! isset($seen[$key])) {
                    $seen[$key] = (int) $card->id;

                    continue;
                }

                $this->retireDuplicate($card);
                $removed++;
            }

            $this->ensureSingleDefault($company);

            return $removed;
        });
    }

    /**
     * Soft-delete duplicate active cards across all companies.
     */
    public function dedupeAll(): int
    {
        $total = 0;

        Company::query()
            ->whereIn('id', SavedPaymentCard::query()->select('company_id')->distinct())
            ->orderBy('id')
            ->each(function (Company $company) use (&$total) {
                $total += $this->dedupeForCompany($company);
            });

        return $total;
    }

    /**
     * @param  array{nickname?: ?string, holder?: ?string, is_default?: bool}  $data
     */
    public function update(Company $company, SavedPaymentCard $card, array $data): SavedPaymentCard
    {
        if ((int) $card->company_id !== (int) $company->id) {
            abort(403);
        }

        return DB::transaction(function () use ($company, $card, $data) {
            $nickname = array_key_exists('nickname', $data)
                ? (trim((string) $data['nickname']) ?: null)
                : $card->nickname;
            $holder = array_key_exists('holder', $data)
                ? (trim((string) $data['holder']) ?: null)
                : $card->holder;

            $card->forceFill([
                'nickname' => $nickname !== null ? mb_substr($nickname, 0, 80) : null,
                'holder' => $holder !== null ? mb_substr($holder, 0, 120) : null,
            ])->save();

            if (! empty($data['is_default'])) {
                return $this->setDefault($company, $card->fresh());
            }

            return $card->fresh();
        });
    }

    public function setDefault(Company $company, SavedPaymentCard $card): SavedPaymentCard
    {
        if ((int) $card->company_id !== (int) $company->id) {
            abort(403);
        }

        return DB::transaction(function () use ($company, $card) {
            SavedPaymentCard::query()
                ->where('company_id', $company->id)
                ->update(['is_default' => false]);

            $card->forceFill([
                'is_default' => true,
                'status' => 'active',
            ])->save();

            return $card->fresh();
        });
    }

    public function delete(Company $company, SavedPaymentCard $card): void
    {
        if ((int) $card->company_id !== (int) $company->id) {
            abort(403);
        }

        $wasDefault = $card->is_default;
        $card->delete();

        if ($wasDefault) {
            $this->ensureSingleDefault($company);
        }
    }

    public function defaultFor(Company $company): ?SavedPaymentCard
    {
        return SavedPaymentCard::query()
            ->where('company_id', $company->id)
            ->where('status', 'active')
            ->where('is_default', true)
            ->first()
            ?: SavedPaymentCard::query()
                ->where('company_id', $company->id)
                ->where('status', 'active')
                ->orderByDesc('id')
                ->first();
    }

    protected function resolveKeeper(
        Company $company,
        string $registrationId,
        ?string $brand,
        ?string $lastFour,
        ?string $expiryMonth,
        ?string $expiryYear,
    ): ?SavedPaymentCard {
        $byRegistration = SavedPaymentCard::withTrashed()
            ->where('company_id', $company->id)
            ->where('registration_id', $registrationId)
            ->first();

        $byFingerprint = null;
        if ($this->hasFingerprint($brand, $lastFour, $expiryMonth, $expiryYear)) {
            $byFingerprint = SavedPaymentCard::withTrashed()
                ->where('company_id', $company->id)
                ->where('brand', $brand)
                ->where('last_four', $lastFour)
                ->where('expiry_month', $expiryMonth)
                ->where(function ($query) use ($expiryYear) {
                    $query->where('expiry_year', $expiryYear)
                        ->orWhere('expiry_year', substr((string) $expiryYear, -2));
                    if (strlen((string) $expiryYear) === 2) {
                        $query->orWhere('expiry_year', '20'.$expiryYear);
                    }
                })
                ->orderByRaw('case when deleted_at is null then 0 else 1 end')
                ->orderByDesc('is_default')
                ->orderByDesc('id')
                ->first();
        }

        if ($byRegistration && $byFingerprint && (int) $byRegistration->id !== (int) $byFingerprint->id) {
            // Prefer the fingerprint row (same physical card / nickname), retire the other.
            $this->retireDuplicate($byRegistration);

            return $byFingerprint;
        }

        return $byRegistration ?: $byFingerprint;
    }

    protected function retireFingerprintSiblings(
        Company $company,
        ?string $brand,
        ?string $lastFour,
        ?string $expiryMonth,
        ?string $expiryYear,
        ?int $keepId,
    ): void {
        $query = SavedPaymentCard::withTrashed()
            ->where('company_id', $company->id)
            ->where('brand', $brand)
            ->where('last_four', $lastFour)
            ->where('expiry_month', $expiryMonth)
            ->where(function ($q) use ($expiryYear) {
                $q->where('expiry_year', $expiryYear)
                    ->orWhere('expiry_year', substr((string) $expiryYear, -2));
                if (strlen((string) $expiryYear) === 2) {
                    $q->orWhere('expiry_year', '20'.$expiryYear);
                }
            });

        if ($keepId) {
            $query->where('id', '!=', $keepId);
        }

        $query->get()->each(function (SavedPaymentCard $other) {
            $this->retireDuplicate($other);
        });
    }

    protected function retireDuplicate(SavedPaymentCard $card): void
    {
        if ($card->trashed() && $card->status === 'merged') {
            return;
        }

        $card->forceFill([
            // Free unique (company_id, registration_id) for the keeper.
            'registration_id' => mb_substr($card->registration_id.'_dup'.$card->id, 0, 64),
            'is_default' => false,
            'status' => 'merged',
            'deleted_at' => $card->deleted_at ?: now(),
        ])->save();
    }

    protected function ensureSingleDefault(Company $company): void
    {
        $default = SavedPaymentCard::query()
            ->where('company_id', $company->id)
            ->where('status', 'active')
            ->where('is_default', true)
            ->orderByDesc('id')
            ->first();

        if ($default) {
            SavedPaymentCard::query()
                ->where('company_id', $company->id)
                ->where('id', '!=', $default->id)
                ->update(['is_default' => false]);

            return;
        }

        $next = SavedPaymentCard::query()
            ->where('company_id', $company->id)
            ->where('status', 'active')
            ->orderByDesc('id')
            ->first();

        if ($next) {
            $next->forceFill(['is_default' => true])->save();
        }
    }

    protected function hasFingerprint(
        ?string $brand,
        ?string $lastFour,
        ?string $expiryMonth,
        ?string $expiryYear,
    ): bool {
        return filled($brand) && filled($lastFour) && filled($expiryMonth) && filled($expiryYear);
    }

    protected function fingerprintKey(
        string $brand,
        string $lastFour,
        string $expiryMonth,
        string $expiryYear,
    ): string {
        return strtoupper($brand).'|'.$lastFour.'|'.$expiryMonth.'|'.substr($expiryYear, -2);
    }

    protected function normalizeBrand(mixed $value): ?string
    {
        $brand = strtoupper(trim((string) $value));

        return $brand !== '' ? mb_substr($brand, 0, 32) : null;
    }

    protected function normalizeLastFour(mixed $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value) ?: '';

        if ($digits === '') {
            return null;
        }

        return substr($digits, -4);
    }

    protected function normalizeExpiryMonth(mixed $value): ?string
    {
        $month = preg_replace('/\D+/', '', (string) $value) ?: '';

        if ($month === '') {
            return null;
        }

        return str_pad(substr($month, -2), 2, '0', STR_PAD_LEFT);
    }

    protected function normalizeExpiryYear(mixed $value): ?string
    {
        $year = preg_replace('/\D+/', '', (string) $value) ?: '';

        if ($year === '') {
            return null;
        }

        if (strlen($year) === 2) {
            return '20'.$year;
        }

        return substr($year, -4);
    }

    protected function normalizeBin(mixed $value): ?string
    {
        $bin = preg_replace('/\D+/', '', (string) $value) ?: '';

        return $bin !== '' ? mb_substr($bin, 0, 16) : null;
    }
}
