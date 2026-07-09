<?php

namespace App\Services;

use App\Models\Company;
use App\Models\ReferralCode;
use App\Models\ReferralConversion;
use App\Models\ReferralInvitation;
use App\Models\ReferralPointsLedger;
use App\Models\ReferralProgramSetting;
use App\Models\ReferralVisit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReferralService
{
    public const DEVICE_COOKIE = 'mb_device_id';

    public function settings(): ReferralProgramSetting
    {
        return ReferralProgramSetting::current();
    }

    public function findActiveCode(?string $code): ?ReferralCode
    {
        if (! filled($code)) {
            return null;
        }

        return ReferralCode::query()
            ->where('code', strtoupper(trim($code)))
            ->where('is_active', true)
            ->first();
    }

    public function createCode(array $attributes): ReferralCode
    {
        do {
            $code = strtoupper(Str::random(10));
        } while (ReferralCode::query()->where('code', $code)->exists());

        return ReferralCode::create([
            'code' => $code,
            'tenant_id' => $attributes['tenant_id'] ?? null,
            'employee_id' => $attributes['employee_id'] ?? null,
            'employee_name' => $attributes['employee_name'] ?? null,
            'employee_email' => $attributes['employee_email'] ?? null,
            'is_active' => $attributes['is_active'] ?? true,
        ]);
    }

    public function deviceId(Request $request): string
    {
        $existing = $request->cookie(self::DEVICE_COOKIE);

        if (is_string($existing) && strlen($existing) >= 16) {
            return $existing;
        }

        return (string) Str::uuid();
    }

    public function deviceHash(string $deviceId): string
    {
        return hash('sha256', $deviceId);
    }

    public function recordVisit(ReferralCode $referralCode, Request $request, ?ReferralInvitation $invitation = null): ReferralVisit
    {
        $sessionKey = 'referral_visit_recorded_'.$referralCode->code;
        $existingVisitId = $request->session()->get('referral_visit_id');

        if ($request->session()->get($sessionKey) && $existingVisitId) {
            $existingVisit = ReferralVisit::query()
                ->whereKey($existingVisitId)
                ->where('referral_code_id', $referralCode->id)
                ->first();

            if ($existingVisit !== null) {
                return $existingVisit;
            }
        }

        $deviceId = $this->deviceId($request);
        $visitorHash = $this->deviceHash($deviceId);
        $senderHash = $invitation?->sender_device_hash ?? $referralCode->sender_device_hash;
        $isDistinct = $this->isDistinctDevice($senderHash, $visitorHash);

        $visit = ReferralVisit::create([
            'referral_code_id' => $referralCode->id,
            'referral_invitation_id' => $invitation?->id,
            'visitor_device_hash' => $visitorHash,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
            'is_distinct_device' => $isDistinct,
            'session_id' => $request->session()->getId(),
        ]);

        $request->session()->put('referral_visit_id', $visit->id);
        $request->session()->put('referral_code', $referralCode->code);
        $request->session()->put($sessionKey, true);

        return $visit;
    }

    public function isDistinctDevice(?string $senderHash, string $visitorHash): bool
    {
        if (! filled($senderHash)) {
            return true;
        }

        return ! hash_equals($senderHash, $visitorHash);
    }

    public function promotionalText(ReferralCode $referralCode, ?string $locale = null): string
    {
        $settings = $this->settings();
        $locale = $locale ?? app()->getLocale();
        $template = $locale === 'ar'
            ? ($settings->promotional_template_ar ?: $settings->promotional_template_en)
            : ($settings->promotional_template_en ?: $settings->promotional_template_ar);

        $link = $referralCode->inviteUrl();
        $name = $referralCode->employee_name ?: __('referrals.default_referrer_name');

        return str_replace(
            ['{link}', '{name}', '{code}'],
            [$link, $name, $referralCode->code],
            $template ?: '{link}',
        );
    }

    public function handleRegistrationConversion(
        ?string $referralCodeValue,
        Company $company,
        User $subscriber,
        ?int $planId,
        ?Request $request = null,
    ): ?ReferralConversion {
        if (empty($planId)) {
            return null;
        }

        $settings = $this->settings();

        if (! $settings->is_enabled) {
            return null;
        }

        $referralCode = $this->findActiveCode($referralCodeValue ?? session('referral_code'));

        if ($referralCode === null) {
            return null;
        }

        if (filled($referralCode->employee_email)
            && strcasecmp((string) $referralCode->employee_email, (string) $subscriber->email) === 0) {
            return null;
        }

        $visit = $this->resolveVisitForConversion($referralCode, $request);
        $isDistinct = $visit?->is_distinct_device ?? true;

        if (! $isDistinct) {
            return ReferralConversion::create([
                'referral_code_id' => $referralCode->id,
                'referral_visit_id' => $visit?->id,
                'company_id' => $company->id,
                'subscriber_user_id' => $subscriber->id,
                'plan_id' => $planId,
                'points_awarded' => 0,
                'is_distinct_device' => false,
                'status' => 'rejected',
            ]);
        }

        $points = $this->pointsForPlan($planId);

        if ($points <= 0) {
            return null;
        }

        if ($this->monthlyCapReached($referralCode, $points)) {
            return ReferralConversion::create([
                'referral_code_id' => $referralCode->id,
                'referral_visit_id' => $visit?->id,
                'company_id' => $company->id,
                'subscriber_user_id' => $subscriber->id,
                'plan_id' => $planId,
                'points_awarded' => 0,
                'is_distinct_device' => true,
                'status' => 'capped',
            ]);
        }

        return DB::transaction(function () use ($referralCode, $visit, $company, $subscriber, $planId, $points) {
            $conversion = ReferralConversion::create([
                'referral_code_id' => $referralCode->id,
                'referral_visit_id' => $visit?->id,
                'company_id' => $company->id,
                'subscriber_user_id' => $subscriber->id,
                'plan_id' => $planId,
                'points_awarded' => $points,
                'is_distinct_device' => true,
                'status' => 'confirmed',
            ]);

            ReferralPointsLedger::create([
                'referral_code_id' => $referralCode->id,
                'referral_conversion_id' => $conversion->id,
                'points' => $points,
                'reason' => __('referrals.ledger_conversion_reason'),
            ]);

            $referralCode->increment('total_points', $points);

            session()->forget(['referral_code', 'referral_visit_id']);

            return $conversion;
        });
    }

    protected function resolveVisitForConversion(ReferralCode $referralCode, ?Request $request): ?ReferralVisit
    {
        $visitId = $request?->session()->get('referral_visit_id') ?? session('referral_visit_id');

        if ($visitId) {
            $visit = ReferralVisit::query()
                ->whereKey($visitId)
                ->where('referral_code_id', $referralCode->id)
                ->first();

            if ($visit !== null) {
                return $visit;
            }
        }

        return $referralCode->visits()->latest('id')->first();
    }

    public function pointsForPlan(?int $planId): int
    {
        $settings = $this->settings();

        if ($planId && is_array($settings->points_by_plan)) {
            $planPoints = $settings->points_by_plan[(string) $planId] ?? $settings->points_by_plan[$planId] ?? null;

            if ($planPoints !== null) {
                return max(0, (int) $planPoints);
            }
        }

        return max(0, (int) $settings->default_points_per_conversion);
    }

    protected function monthlyCapReached(ReferralCode $referralCode, int $incomingPoints): bool
    {
        $cap = $this->settings()->monthly_points_cap;

        if (! $cap) {
            return false;
        }

        $earnedThisMonth = ReferralPointsLedger::query()
            ->where('referral_code_id', $referralCode->id)
            ->where('points', '>', 0)
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum('points');

        return ($earnedThisMonth + $incomingPoints) > $cap;
    }
}
