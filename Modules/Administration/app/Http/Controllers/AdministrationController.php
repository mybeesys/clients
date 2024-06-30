<?php

namespace Modules\Administration\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdministrationController extends Controller
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
}
