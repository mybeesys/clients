<?php

namespace App\Services;

use App\Models\ReferralCode;
use App\Models\ReferralConversion;
use App\Models\ReferralInvitation;
use App\Models\ReferralPointsLedger;
use App\Models\ReferralVisit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReferralStatsService
{
    /**
     * @return array{
     *     referrers:int,
     *     invitations:int,
     *     visits:int,
     *     distinct_visits:int,
     *     conversions:int,
     *     confirmed_conversions:int,
     *     total_points:int,
     *     conversion_rate:float
     * }
     */
    public function overview(): array
    {
        $referrers = ReferralCode::query()->count();
        $invitations = ReferralInvitation::query()->count();
        $visits = ReferralVisit::query()->count();
        $distinctVisits = ReferralVisit::query()->where('is_distinct_device', true)->count();
        $conversions = ReferralConversion::query()->count();
        $confirmed = ReferralConversion::query()->where('status', 'confirmed')->count();
        $totalPoints = (int) ReferralCode::query()->sum('total_points');
        $rate = $distinctVisits > 0 ? round(($confirmed / $distinctVisits) * 100, 1) : 0.0;

        return [
            'referrers' => $referrers,
            'invitations' => $invitations,
            'visits' => $visits,
            'distinct_visits' => $distinctVisits,
            'conversions' => $conversions,
            'confirmed_conversions' => $confirmed,
            'total_points' => $totalPoints,
            'conversion_rate' => $rate,
        ];
    }

    /**
     * @return Collection<int, ReferralCode>
     */
    public function topReferrers(int $limit = 10): Collection
    {
        return ReferralCode::query()
            ->withCount([
                'invitations',
                'visits',
                'conversions as confirmed_conversions_count' => fn ($query) => $query->where('status', 'confirmed'),
            ])
            ->orderByDesc('total_points')
            ->orderByDesc('confirmed_conversions_count')
            ->limit($limit)
            ->get();
    }

    /**
     * @return array<string, int>
     */
    public function conversionsByStatus(): array
    {
        return ReferralConversion::query()
            ->select('status', DB::raw('count(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->all();
    }

    public function performanceScore(ReferralCode $code): string
    {
        $points = (int) $code->total_points;
        $confirmed = $code->confirmed_conversions_count
            ?? $code->conversions()->where('status', 'confirmed')->count();

        return match (true) {
            $points >= 100 || $confirmed >= 10 => 'A',
            $points >= 50 || $confirmed >= 5 => 'B',
            $points >= 20 || $confirmed >= 2 => 'C',
            $points > 0 || $confirmed > 0 => 'D',
            default => '—',
        };
    }

    public function adjustPoints(ReferralCode $code, int $points, string $reason): void
    {
        DB::transaction(function () use ($code, $points, $reason) {
            ReferralPointsLedger::create([
                'referral_code_id' => $code->id,
                'points' => $points,
                'reason' => $reason,
            ]);

            $code->increment('total_points', $points);
        });
    }
}
