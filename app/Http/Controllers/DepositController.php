<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Requisition;
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

    public function balancePaymentFund(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'funded' => 'boolean',
            'requisition_id' => 'required|exists:requisitions,id',
        ]);

        if ($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 422);
        }

         //here we want to update the requisition funding status
         $requisition = Requisition::find($request->input('requisition_id'));
         if($requisition){
             $requisition->update([
                 'funding_status' => 1, // true should be a boolean, not a string
                 'capturing_status' => 1,
                 'status_id' => 3, //chainge status to awaiting authorization 
                 'locked' => 1
             ]);
         }

         // Create the deposit record and associate it with the authenticated user
        $deposit = Deposit::create([
            'description' => $request->input('description'),
            'amount' => $request->input('amount'),
            'funded' => $request->input('funded', false),
            'deposit_date' => null,
            'firm_account_id' => $request->input('firm_account_id'),
            'requisition_id' => $request->input('requisition_id'),
            'user_id' => auth()->id(),  // Save the authenticated user's ID
        ]);

       // Eager load the user relationship and return the deposit with user data
        $deposit->load('user');

        return response()->json($deposit, 201); // Return the created deposit with user data

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

        //here we want to update the requisition funding status
        $requisition = Requisition::find($request->input('requisition_id'));
        if($requisition){
            $requisition->update([
                'funding_status' => 1, // true should be a boolean, not a string
            ]);
        }

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

       // Eager load the user relationship and return the deposit with user data
        $deposit->load('user');

        return response()->json($deposit, 201); // Return the created deposit with user data
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
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'funded' => 'boolean',  // Ensure funded is a boolean
            'deposit_date' => 'nullable|date|after_or_equal:today' // deposit_date should be a valid date or nullable
        ]);

        // Handle the logic based on whether the deposit is funded or not
        if ($request->funded) {
            // If the deposit is funded, set deposit_date to null
            $validated['deposit_date'] = null;
        } else {
            // If the deposit is not funded, use the provided deposit_date
            $validated['deposit_date'] = $request->deposit_date;
        }

        $deposit->update($validated);

        // Eager load the user relationship and return the deposit with user data
        $deposit->load('user');

        return response()->json($deposit);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Deposit $deposit)
    {
        // Get the requisition associated with the deposit
        $requisition = $deposit->requisition;

        // Check if this is the last deposit for the requisition
        $remainingDeposits = $requisition->deposits()->count();

        if ($remainingDeposits === 1) {
            // If this is the last deposit, set funding_status to null
            $requisition->update(['funding_status' => null]);
        }

        // Delete the deposit
        $deposit->delete();

        return response()->json($requisition, 201);
    }
}
