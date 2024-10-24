<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DepositController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        // Validate the input
        $validator = Validator::make($request->all(), [
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'funded' => 'boolean',
            //'deposit_date' => 'nullable|date|after_or_equal:today',
            'requisition_id' => 'required|exists:requisitions,id',
        ]);

        // Handle validation failure
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

         // If funded is true, set deposit_date to null
        $depositDate = $request->input('funded') ? null : $request->input('deposit_date');

        // Create the deposit record and associate it with the authenticated user
        $deposit = Deposit::create([
            'description' => $request->input('description'),
            'amount' => $request->input('amount'),
            'funded' => $request->input('funded', false),
            'deposit_date' => $depositDate,
            'firm_account_id' => $request->input('firm_account_id'),
            'requisition_id' => $request->input('requisition_id'),
            'user_id' => auth()->id(),  // Save the authenticated user's ID
        ]);

        return response()->json($deposit, 201); // Success
    }

    /**
     * Display the specified resource.
     */
    public function show(Deposit $deposit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Deposit $deposit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Deposit $deposit)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Deposit $deposit)
    {
        //
    }
}
