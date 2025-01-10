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
        return DB::transaction(function () use ($request) {
            $plan = Plan::find($request->id);
            $user = auth()->user();
            $company = Company::firstWhere('user_id', $user->id);

            $company->subscribeTo($plan);

            $company->forceFill([
                'subscribed' => true
            ])->save();
            $domain = $user->tenant->domains->first()->domain;
            $protocol = request()->secure() ? 'https://' : 'http://';
            return redirect($protocol . $domain);
        });
    }
}
