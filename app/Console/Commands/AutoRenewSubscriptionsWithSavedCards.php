<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\CompanyEntitlement;
use App\Services\Entitlements\EntitlementPricer;
use App\Services\HyperPay\HyperPayCheckoutService;
use App\Services\HyperPay\SavedPaymentCardService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoRenewSubscriptionsWithSavedCards extends Command
{
    protected $signature = 'subscriptions:auto-renew-saved-cards
                            {--days=3 : Renew when expiry is within this many days (including already expired)}
                            {--dry-run : List matches without charging}';

    protected $description = 'Automatically renew due subscriptions using each company\'s default saved card';

    public function handle(
        EntitlementPricer $pricer,
        SavedPaymentCardService $cards,
        HyperPayCheckoutService $checkout,
    ): int {
        $days = max(0, (int) $this->option('days'));
        $dryRun = (bool) $this->option('dry-run');
        $cutoff = Carbon::now()->addDays($days)->endOfDay();

        $entitlements = CompanyEntitlement::query()
            ->with(['company.subscription'])
            ->whereHas('company.subscription', function ($query) use ($cutoff) {
                $query->whereNotNull('expired_at')
                    ->whereNull('suppressed_at')
                    ->where('expired_at', '<=', $cutoff);
            })
            ->get();

        $ok = 0;
        $fail = 0;

        foreach ($entitlements as $entitlement) {
            /** @var Company|null $company */
            $company = $entitlement->company;
            if (! $company) {
                continue;
            }

            $card = $cards->defaultFor($company);
            if (! $card) {
                $this->line("skip company #{$company->id}: no default card");
                continue;
            }

            $planConfig = [
                'intent' => 'renew',
                'period' => ($entitlement->period ?? 'Year') === 'Month' ? 'Month' : 'Year',
                'modules' => $entitlement->modules ?? [],
                'employees' => (int) $entitlement->employees_quota,
                'establishments' => (int) $entitlement->establishments_quota,
                'screen_devices' => (int) $entitlement->screen_devices_quota,
                'pos_devices' => (int) ($entitlement->pos_devices_quota ?? 0),
                'waiter_seats' => (int) ($entitlement->waiter_seats_quota ?? 0),
                'kitchen_devices' => (int) ($entitlement->kitchen_devices_quota ?? 0),
                'coupon_code' => null,
            ];

            try {
                $quote = $pricer->quote($planConfig);
            } catch (\Throwable $e) {
                $fail++;
                $this->error("quote failed company #{$company->id}: ".$e->getMessage());
                continue;
            }

            if ($dryRun) {
                $this->info("dry-run company #{$company->id} card {$card->label()} amount {$quote['period_total']}");
                $ok++;
                continue;
            }

            try {
                $payment = $checkout->chargeSavedCard(
                    $company,
                    $planConfig,
                    $quote,
                    HyperPayCheckoutService::billingFromCompany($company, $company->user),
                    'renew',
                    $card,
                );

                if ($payment->isPaid()) {
                    $ok++;
                    $this->info("renewed company #{$company->id} via {$card->label()}");
                } else {
                    $fail++;
                    $this->warn("pending/failed company #{$company->id}: {$payment->result_description}");
                }
            } catch (\Throwable $e) {
                $fail++;
                Log::warning('auto renew saved card failed', [
                    'company_id' => $company->id,
                    'error' => $e->getMessage(),
                ]);
                $this->error("charge failed company #{$company->id}: ".$e->getMessage());
            }
        }

        $this->info("done. ok={$ok} fail={$fail}");

        return self::SUCCESS;
    }
}
