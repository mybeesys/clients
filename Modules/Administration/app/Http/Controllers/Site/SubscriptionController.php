<?php

namespace Modules\Administration\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Administration\Events\TenantCreated;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Modules\Administration\Models\Plan;
use App\Models\Company;
use Modules\Administration\Models\Coupon;
use Modules\Company\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;
use Stripe\Charge;

class SubscriptionController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('administration::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('administration::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->guard('company')->user();
        $plan = Plan::find($request->plan_id);
        $company = Company::find($user->company->first()->id);
        $coupon_code = $request->input('coupon_code');

        $price = $plan->price;

        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
        $redirect_url = route('site.company.subscribe.confirmation') . '?session_id={CHECKOUT_SESSION_ID}';

        if ($coupon_code && !empty($coupon_code) && Coupon::where('code', $coupon_code)->exists()) {
            $coupon = Coupon::where('code', $coupon_code)
                ->where('active', 1)
                ->where('expired_at', '>=', now())
                ->whereColumn('times_used', '<=', 'max_use')
                ->first();

            if ($coupon) {
                $stripe_coupon = null;

                try {
                    $stripe_coupon = $stripe->coupons->retrieve($coupon->code, []);
                } catch (\Stripe\Exception\InvalidRequestException $e) {
                    if ($coupon->descount_type == 'Fixed') {
                        $stripe_coupon = $stripe->coupons->create([
                            'amount_off' => $coupon->amount * 100,
                            'currency' => 'USD',
                            'name' => $coupon->name,
                            'id' => $coupon_code
                        ]);
                    } elseif ($coupon->descount_type == 'Percentage') {
                        $stripe_coupon = $stripe->coupons->create([
                            'percent_off' => $coupon->amount,
                            'name' => $coupon->name,
                            'id' => $coupon_code
                        ]);
                    }
                }

                $coupon->increment('times_used');
            } else {
                return back()->with('error', 'Invalid or expired coupon code.');
            }
        }


        $session_data = [
            'success_url' => $redirect_url,
            'payment_method_types' => ['link', 'card'],
            'metadata' => [
                'plan_id' => $plan->id,
                'company_id' => $company->id,
            ],
            'line_items' => [
                [
                    'price_data'  => [
                        'product_data' => [
                            'name' => $plan->name,
                        ],
                        'unit_amount'  => 100 * $price,
                        'currency'     => 'USD',
                    ],
                    'quantity'    => 1,
                ],
            ],
            'mode' => 'payment',
        ];

        if (isset($stripe_coupon) && $stripe_coupon->id) {
            $session_data['discounts'] = [
                [
                    'coupon' => $stripe_coupon->id,
                ],
            ];
        }

        $response = $stripe->checkout->sessions->create($session_data);

        return redirect($response['url']);
    }

    public function confirmation(Request $request)
    {
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));

        $session = $stripe->checkout->sessions->retrieve($request->session_id);

        if ($session->status == 'complete') {
            $user = auth()->guard('company')->user();
            $plan = Plan::find($session->metadata->plan_id);
            $company = Company::find($user->company->first()->id);

            $this->handleSubscription($request, $user, $plan, $company);

            return redirect()->back()->with('success', 'Subscribed successfully wait until the admin activate your account.');
        } else {
            return redirect()->route('site.company.subscribe')->with('error', 'Payment failed or was canceled.');
        }
    }



    protected function handleSubscription($request, $user, $plan, $company)
    {
        $currentDate = Carbon::now()->format('Ymd') . Carbon::now()->timestamp;
        $subdomain_name = strtolower($user->name) . '-' . $currentDate;

        $tenant = Tenant::create([
            'id' => $subdomain_name,
            'company_id' => $company->id,
            'tenancy_db_name' => $subdomain_name . '_db'
        ]);

        $domain = new Domain([
            'domain' => $subdomain_name .  env('BASE_DOMAIN'),
        ]);

        $tenant->domains()->save($domain);

        $subscription = $company->subscribeTo($plan);
        $subscription->update(['tenant_id' => $tenant->id, 'subdomain' => $domain->domain]);
        $company->subscribed = 1;
        $company->save();


        // Config::set('database.connections.mysql.database', $domain->tenant_id . '_db');
        // DB::purge('mysql');
        // DB::reconnect('mysql');
        // $tenantPlan = $plan->replicate();
        // $tenantPlan->save();

        // $tenantSubscription = $subscription->replicate();
        // $tenantSubscription->save();

        // event(new TenantCreated($tenant));
    }



    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('administration::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('administration::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
