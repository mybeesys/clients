<?php

namespace App\Http\Controllers;

use App\Models\Feature;
use App\Models\Plan;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class InviteLandingController extends Controller
{
    public function __construct(protected ReferralService $referrals) {}

    public function __invoke(Request $request, string $code): Response
    {
        if ($request->filled('set_lang')) {
            $locale = $request->query('set_lang') === 'en' ? 'en' : 'ar';
            session(['locale' => $locale]);
            app()->setLocale($locale);

            return redirect()->route('referrals.landing', ['code' => $code]);
        }

        $referralCode = $this->referrals->findActiveCode($code);

        if ($referralCode === null) {
            abort(404);
        }

        $deviceId = $this->referrals->deviceId($request);
        $this->referrals->recordVisit($referralCode, $request);

        $plans = Plan::query()->where('active', true)->orderBy('price')->get();
        $features = Feature::query()->whereHas('feature_plans')->get();

        $response = response()->view('referrals.landing', [
            'referralCode' => $referralCode,
            'plans' => $plans,
            'features' => $features,
            'promotionalText' => $this->referrals->promotionalText($referralCode),
            'registerUrl' => route('filament.admin.auth.register', ['ref' => $referralCode->code]),
        ]);

        if (! $request->hasCookie(ReferralService::DEVICE_COOKIE)) {
            $response->withCookie(Cookie::make(
                ReferralService::DEVICE_COOKIE,
                $deviceId,
                60 * 24 * 365,
                '/',
                null,
                $request->secure(),
                true,
                false,
                'lax',
            ));
        }

        return $response;
    }
}
