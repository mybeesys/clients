<?php

namespace Modules\Administration\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Administration\Events\TenantCreated;

use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Modules\Administration\Models\Plan;
use Modules\Company\Models\Company;
use Modules\Company\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;

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
        // try {
        //     DB::beginTransaction();
        dd(env('BASE_DOMAIN'));
        $user =  auth()->guard('company')->user();
        $currentDate = Carbon::now()->format('Ymd') . Carbon::now()->timestamp;
        $subdomain_name = strtolower($user->name) . '-' . $currentDate;
        $tenant = Tenant::create([
            'id' => $subdomain_name,
            'company_id' => $user->company->id,
            'tenancy_db_name' => $subdomain_name . '_db'
        ]);


        $domain = new Domain([
            'domain' =>    $subdomain_name .  env('BASE_DOMAIN'),
        ]);


        $tenant->domains()->save($domain);

        $plan = Plan::find($request->plan_id);
        $company = Company::find($user->company->id);

        $subscription = $company->subscribeTo($plan);

        $company->subscribed = 1;
        $company->save();

        //insert the information of the plan/subscription in the tenant database.
        Config::set('database.connections.mysql.database', $domain->tenant_id . '_db');
        DB::purge('mysql');
        DB::reconnect('mysql');
        $tenantPlan = $plan->replicate();
        $tenantPlan->save();


        //send and event to make a tenant seeder.
        event(new TenantCreated($tenant));
        $tenantSubscription = $subscription->replicate();
        $tenantSubscription->save();

        DB::table('tenants')->insert([
            'id' => $subdomain_name,
            'tenancy_db_name' => $tenant->id . '_db',
            'created_at' => now(),
            'updated_at' => now(),
            'data' => null
        ]);

        DB::table('domains')->insert([
            "domain" => $tenant->id . "erp.localhost",
            "tenant_id" => $tenant->id,
            'created_at' => now(),
            'updated_at' => now(),

        ]);



        //     DB::commit();
        //     return redirect()->route('site.company.plans_subscription_page');
        // } catch (\Throwable $th) {
        //     DB::rollBack();
        //     return redirect()->back();
        // }
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
