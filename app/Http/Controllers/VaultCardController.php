<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Services\HyperPay\HyperPayCheckoutService;
use App\Services\HyperPay\HyperPayConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

/**
 * Starts a registration-only vault session and sends the shopper
 * straight to the compact MyBee card form (no ManageSubscription UI).
 */
class VaultCardController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        abort_unless(Auth::check(), 403);

        $company = $this->company();
        abort_unless($company, 403);

        if (! HyperPayConfig::instance()->isConfigured()) {
            return redirect()
                ->route('subscribe.embed-error', ['reason' => 'unauthenticated'])
                ->with('error', __('fields.hyperpay_not_configured'));
        }

        $billing = HyperPayCheckoutService::billingFromCompany($company, Auth::user());
        $token = Str::random(40);

        try {
            $payment = HyperPayCheckoutService::instance()->startSaveCard($company, $billing, $token);
        } catch (InvalidArgumentException|RuntimeException $exception) {
            report($exception);

            return redirect()
                ->route('subscribe.embed-error', ['reason' => 'invalid'])
                ->with('error', $exception->getMessage());
        }

        session([
            'hyperpay.checkout_uid' => $payment->uid,
            'hyperpay.checkout_token' => $token,
            'subscribe.embed' => true,
        ]);

        return redirect()->away(route('hyperpay.checkout', ['uid' => $payment->uid], absolute: true));
    }

    protected function company(): ?Company
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (! $user) {
            return null;
        }

        $handoffCompanyId = session('subscribe.company_id');
        if ($handoffCompanyId) {
            $company = Company::query()
                ->where('id', (int) $handoffCompanyId)
                ->where(function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->orWhereHas('members', fn ($members) => $members->where('users.id', $user->id));
                })
                ->first();

            if ($company) {
                return $company;
            }
        }

        return $user->defaultCompany() ?? $user->company;
    }
}
