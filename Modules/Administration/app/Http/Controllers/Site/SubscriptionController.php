<?php

namespace Modules\Administration\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Services\SubdomainService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Administration\Models\Plan;
use Modules\Company\Models\Tenant;

class SubscriptionController extends Controller
{
    protected $subdomainService;


    public function __construct(SubdomainService $subdomainService)
    {
        $this->subdomainService = $subdomainService;
    }
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
    public function store(Request $request): RedirectResponse
    {
        //
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
    public function update(Request $request, $id): RedirectResponse
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
            'domain' => $user->name . 'Company' . $currentDate.'.erp.localhost',
            'company_id' => $company_id

        ]);

        dd($tenant);

        $res =  $this->subdomainService->create_subdomain($subdomain_name);
        dd($res);

        //     DB::commit();
        // } catch (\Throwable $th) {
        //     DB::rollBack();
        //     return redirect()->back();
        // }
    }


    public function create_subdomain(Request $request)
    {
        $subdomain = $request->input('subdomain');

        $result = $this->subdomainService->create_subdomain($subdomain);

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 500);
        }

        return response()->json(['message' => $result['message']]);
    }
}
