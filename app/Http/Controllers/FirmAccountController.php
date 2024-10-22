<?php

namespace App\Http\Controllers;

use App\Models\FirmAccount;
use Illuminate\Http\Request;
use DataTables;

class FirmAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get the FirmAccount data with related 'institution'
        $firmaccounts = FirmAccount::with('institution','category','accounttype');

        // Use the DataTables facade to return data in the required format
        return DataTables::of($firmaccounts)->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(FirmAccount $firmAccount)
    {
        // Return the firmAccount with any required relationships, such as the user who created it
        return response()->json($firmAccount->load('institution'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FirmAccount $firmAccount)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FirmAccount $firmAccount)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FirmAccount $firmAccount)
    {
        //
    }
}
