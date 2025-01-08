<?php

namespace App\Http\Controllers;

use App\Models\Company;
use DB;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $plan_id = $request->id;
            $user = auth()->user();
            $company = Company::firstWhere('user_id', $user->id);
            $company->subscriptions()->create([
                'plan_id' => $plan_id,
                'started_at' => now(),
                'expired_at' => now()->addYears(10),
                'grace_days_ended_at' => now()->addYears(10)->addMonth(),
                'suppressed_at' => now()->addYears(10)->addMonth()
            ]);
            $company->update([
                'subscribed' => true
            ]);
            $domain = $user->tenant->domains->first()->domain;
            $protocol = request()->secure() ? 'https://' : 'http://';
            return redirect($protocol . $domain);
        });
    }
}
