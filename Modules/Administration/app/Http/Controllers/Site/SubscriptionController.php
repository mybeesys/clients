<?php

namespace Modules\Administration\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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
        DB::beginTransaction();
        try {
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

            $plan = \LucasDotVin\Soulbscription\Models\Plan::find($request->plan_id);
            $company = Company::find($user->company->id);
            $company->subscribeTo($plan);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back();
        }
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

    public function subscribe(Request $request)
    {
        // DB::beginTransaction();
        // try {
        //create sub domain with the company name and details
        //create a db for the company
        //create the subscription record in the main database and in this company database company_id , plan_id
        //make a payment
        $plan = Plan::find($request->plan_id);
        $user =  auth()->guard('company')->user();
        $company_id = $user->company->id;
        $currentDate = Carbon::now()->format('Ymd');
        $subdomain_name = strtolower($user->name) . '-' . $currentDate;
        $tenant = Tenant::create([
            'domain' => $user->name . 'Company' . $currentDate . '.erp.localhost',
            'company_id' => $company_id

        ]);

        dd($tenant);


        //     DB::commit();
        // } catch (\Throwable $th) {
        //     DB::rollBack();
        //     return redirect()->back();
        // }
    }
}
