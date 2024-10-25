<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
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
        // Validate the request data
        $validated = $request->validate([
            'firm_account_id' => 'required|exists:firm_accounts,id',
            'requisition_id' => 'required|exists:requisitions,id',
            'category' => 'required|exists:categories,id',
            'account_holder_type' => 'required|in:natural,juristic',
            'initials' => 'nullable|string|max:10',
            'surname' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'registration_number' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'account_holder' => 'nullable|string|max:255',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'my_reference' => 'required|string|max:255',
            'recipient_reference' => 'nullable|string|max:255',
            'branch_code' => 'nullable|string|max:10',
            'id_number' => 'nullable|string|max:255',
            'verified' => 'nullable|boolean',
        ]);

        // Create the payment record
        $payment = Payment::create([
            'firm_account_id' => $validated['firm_account_id'],
            'requisition_id' => $validated['requisition_id'],
            'category_id' => $validated['category'],
            'account_holder_type' => $validated['account_holder_type'],
            'initials' => $validated['initials'],
            'surname' => $validated['surname'],
            'company_name' => $validated['company_name'],
            'registration_number' => $validated['registration_number'],
            'account_number' => $validated['account_number'],
            'account_holder' => $request->input('account_holder'),
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'my_reference' => $validated['my_reference'],
            'recipient_reference' => $validated['recipient_reference'],
            'institution_id' => $request->institution['id'],
            'account_type_id' => $request->account_type['id'],
            'branch_code' => $validated['branch_code'],
            'id_number' => $validated['id_number'],
            'user_id' => auth()->id(),  // Associate the current authenticated user
            'authorised' => $validated['authorised'] ?? false,
            'verified' => $validated['verified'] ?? false,
        ]);

        return response()->json([
            'message' => 'Payment created successfully',
            'data' => $payment
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payment $payment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $payment)
    {
        // Validate the request data
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'account_holder_type' => 'required|in:natural,juristic',
            'initials' => 'nullable|string|max:10',
            'surname' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'registration_number' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'account_holder' => 'nullable|string|max:255',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'my_reference' => 'required|string|max:255',
            'recipient_reference' => 'nullable|string|max:255',
            'institution_id' => 'required|exists:institutions,id',
            'account_type_id' => 'required|exists:account_types,id',
            'branch_code' => 'nullable|string|max:10',
            'id_number' => 'nullable|string|max:255',
            'verified' => 'boolean',
        ]);

        // Update the payment record
        $payment->update([
            'category_id' => $validated['category_id'],
            'account_holder_type' => $validated['account_holder_type'],
            'initials' => $validated['initials'],
            'surname' => $validated['surname'],
            'company_name' => $validated['company_name'],
            'registration_number' => $validated['registration_number'],
            'account_number' => $validated['account_number'],
            'account_holder' => $validated['account_holder'],
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'my_reference' => $validated['my_reference'],
            'recipient_reference' => $validated['recipient_reference'],
            'institution_id' => $validated['institution_id'],
            'account_type_id' => $validated['account_type_id'],
            'branch_code' => $validated['branch_code'],
            'id_number' => $validated['id_number'],
            'user_id' => auth()->id(),  // Associate the current authenticated user
            'verified' => $validated['verified'] ?? false,
        ]);

        return response()->json([
            'message' => 'Payment updated successfully',
            'data' => $payment
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
         // Get the requisition associated with the deposit
         $requisition = $payment->requisition;

         // Check if this is the last deposit for the requisition
         $remainingDeposits = $requisition->payments()->count();
 
         if ($remainingDeposits === 1) {
             // If this is the last deposit, set funding_status to null
             $requisition->update(['funding_status' => null]);
         }
 
        $payment->delete();
        return response()->json($requisition, 201);
    }
}
