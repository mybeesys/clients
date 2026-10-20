<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\HyperPay\SavedPaymentCardService;
use Illuminate\Console\Command;

class DeduplicateSavedPaymentCards extends Command
{
    protected $signature = 'saved-cards:dedupe
                            {--company= : Limit to a single company id}';

    protected $description = 'Soft-delete duplicate saved cards that share brand + last4 + expiry per company';

    public function handle(SavedPaymentCardService $cards): int
    {
        $companyId = (int) $this->option('company');

        if ($companyId > 0) {
            $company = Company::query()->find($companyId);
            if (! $company) {
                $this->error('Company not found.');

                return self::FAILURE;
            }

            $removed = $cards->dedupeForCompany($company);
            $this->info("Removed {$removed} duplicate card(s) for company #{$companyId}.");

            return self::SUCCESS;
        }

        $removed = $cards->dedupeAll();
        $this->info("Removed {$removed} duplicate card(s) across all companies.");

        return self::SUCCESS;
    }
}
