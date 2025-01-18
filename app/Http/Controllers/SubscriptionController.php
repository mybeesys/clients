<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Plan;
use DB;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => ['int', 'exists:plans,id', 'required']
        ]);
        return DB::transaction(function () use ($validated) {
            $plan = Plan::find($validated['id']);
            $user = auth()->user();
            $company = Company::firstWhere('user_id', $user->id);

            $company->subscribeTo($plan);

            $domain = $user->tenant->domains->first()->domain;
            $protocol = request()->secure() ? 'https://' : 'http://';
            return redirect($protocol . $domain);
        });
    }

    public function switchPlan(Request $request)
    {
        $user = auth()->user();
        if ($user->company->subscription) {
            $validated = $request->validate([
                'id' => ['int', 'exists:plans,id', 'required']
            ]);
            $plan = Plan::find($validated['id']);
            $company = Company::firstWhere('user_id', $user->id);
            $company->switchTo($plan);
            $domain = $user->tenant->domains->first()->domain;
            $protocol = request()->secure() ? 'https://' : 'http://';
            return redirect($protocol . $domain);
        } else {
            return redirect(route('subscribe'));
        }
    }
}
