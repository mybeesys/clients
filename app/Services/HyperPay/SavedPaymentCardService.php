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
            ?: ''
        );

        // Standalone registration responses often use `id` as the registration token.
        if ($registrationId === '' && $payment?->source === HyperPayPayment::SOURCE_SAVE_CARD) {
            $registrationId = (string) data_get($gatewayResponse, 'id', '');
        }

        if ($registrationId === '') {
            return null;
        }

        return DB::transaction(function () use ($company, $gatewayResponse, $payment, $user, $registrationId) {
            $existing = SavedPaymentCard::withTrashed()
                ->where('company_id', $company->id)
                ->where('registration_id', $registrationId)
                ->first();

            // Last successfully charged card becomes the company default for auto-renew.
            SavedPaymentCard::query()
                ->where('company_id', $company->id)
                ->update(['is_default' => false]);

            $card = SavedPaymentCard::withTrashed()->updateOrCreate(
                [
                    'company_id' => $company->id,
                    'registration_id' => $registrationId,
                ],
                [
                    'user_id' => $user?->id ?? $existing?->user_id,
                    'hyperpay_payment_id' => $payment?->id,
                    'payment_id' => data_get($gatewayResponse, 'id'),
                    'brand' => data_get($gatewayResponse, 'paymentBrand'),
                    'card_bin' => data_get($gatewayResponse, 'card.bin'),
                    'last_four' => data_get($gatewayResponse, 'card.last4Digits'),
                    'holder' => data_get($gatewayResponse, 'card.holder'),
                    'expiry_month' => data_get($gatewayResponse, 'card.expiryMonth'),
                    'expiry_year' => data_get($gatewayResponse, 'card.expiryYear'),
                    'is_default' => true,
                    'status' => 'active',
                    'gateway_payload' => [
                        'registrationId' => $registrationId,
                        'paymentBrand' => data_get($gatewayResponse, 'paymentBrand'),
                        'card' => data_get($gatewayResponse, 'card'),
                        'standingInstruction' => data_get($gatewayResponse, 'standingInstruction'),
                    ],
                    'deleted_at' => null,
                ]
            );

            return $card;
        });
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
            $next = SavedPaymentCard::query()
                ->where('company_id', $company->id)
                ->where('status', 'active')
                ->orderByDesc('id')
                ->first();

            if ($next) {
                $next->forceFill(['is_default' => true])->save();
            }
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
}
