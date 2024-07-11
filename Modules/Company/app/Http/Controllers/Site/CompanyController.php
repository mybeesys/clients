<?php

namespace Modules\Company\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Company\Models\Company;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('company::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('company::create');
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
        return view('company::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('company::edit');
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

    public function show_registration_form()
    {
        return view('site.pages.auth.company-registation-page');
    }

    public function show_login_form()
    {
        return view('site.pages.auth.company-login-page');
    }

    public function login(Request $request)
    {
        DB::beginTransaction();
        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required|min:8',
            ]);

            if (Auth::guard('company')->attempt($request->only('email', 'password'))) {
                $user = Auth::guard('company')->user();
                return redirect()->intended('plans');
            }


            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back();
        }
    }


    public function register(Request $request)
    {
        DB::beginTransaction();
        try {

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            $company = Company::create([
                'name' => $user->name,
                'user_id' => $user->id,
            ]);

            auth()->guard('company')->login($user);

            DB::commit();
            return redirect('plans');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back();
        }
    }



    public function logout()
    {
        auth()->guard('company')->logout();
        return redirect()->route('site.company.register.form');
    }
}
