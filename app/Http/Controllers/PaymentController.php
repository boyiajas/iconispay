<?php

namespace App\Http\Controllers;

use App\Models\BeneficiaryAccount;
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
        try {
            // Validate the request data
            $validated = $request->validate([
                'firm_account_id' => 'required|exists:firm_accounts,id',
                'requisition_id' => 'required|exists:requisitions,id',
                'category' => 'required|exists:categories,id',
                //'account_holder_type' => 'required|in:natural,juristic',
                'initials' => 'nullable|string|max:10',
                'surname' => 'nullable|string|max:255',
                'company_name' => 'nullable|string|max:255',
                'registration_number' => 'nullable|string|max:255',
                'account_number' => 'required|string|max:50',
                'institution_id' => 'nullable|exists:institutions,id',
                'description' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0',
                'my_reference' => 'required|string|max:255',
                'recipient_reference' => 'nullable|string|max:255',
                'branch_code' => 'nullable|string|max:10',
                'id_number' => 'nullable|string|max:255',
                'verified' => 'nullable|boolean',
                'existing_beneficiary' => 'required|boolean',
                // Optional: add validation for `verification_status` if needed
            ]);

            // Step 1: Check if the beneficiary account exists or create a new one
            if ($request->existing_beneficiary) {
                $beneficiaryAccount = BeneficiaryAccount::where('account_number', $validated['account_number'])->firstOrFail();

            } else {
                //$beneficiaryAccount = BeneficiaryAccount::where('account_number', $validated['account_number'])->first();

                // If the account does not exist, populate additional fields and save it
                $beneficiaryAccount = BeneficiaryAccount::create([
                    'display_text' => $validated['account_holder_type'] === 'natural' 
                        ? $validated['initials'] . ' ' . $validated['surname'] 
                        : $validated['company_name'],
                    'category_id' => $validated['category'],
                    'account_holder_type' => $validated['account_holder_type'],
                    'initials' => $validated['initials'],
                    'surname' => $validated['surname'],
                    'company_name' => $validated['company_name'],
                    'registration_number' => $validated['registration_number'],
                    'account_number' => $validated['account_number'],
                    'id_number' => $validated['id_number'],
                    'account_type_id' => $request->account_type['id'],
                    'institution_id' => $request->institution['id'],
                    'branch_code' => $validated['branch_code'],
                    'my_reference' => $validated['my_reference'],
                    'recipient_reference' => $validated['recipient_reference'],
                    'authorised' => $request->input('authorised', false),
                    'verified' => $validated['verified'] ?? false,
                    'verification_status' => $validated['verification_status'] ?? null,
                    'user_id' => auth()->id(),
                ]);
            }

            // Step 2: Create the payment record linked to the beneficiary account
            $payment = Payment::create([
                'firm_account_id' => $validated['firm_account_id'],
                'requisition_id' => $validated['requisition_id'],
                'beneficiary_account_id' => $beneficiaryAccount->id,
                'category_id' => $validated['category'],
                'description' => $validated['description'],
                'amount' => $validated['amount'],
                'my_reference' => $validated['my_reference'],
                'recipient_reference' => $validated['recipient_reference'],
                'user_id' => auth()->id(),
                'authorised' => $request->input('authorised', false),
                'verified' => $validated['verified'] ?? false,
            ]);

            // Eager load the user relationship and return the deposit with user data
            $payment->load('beneficiaryAccount');

            return response()->json($payment, 201); // Return the created payment with user data

        } catch (\Exception $e) {
            // Log the error message
            \Log::error('Error creating payment or beneficiary account: ' . $e->getMessage());

            // Return an error response to the client
            return response()->json([
                'message' => 'An error occurred while creating the payment. Please try again later.',
                'error' => $e->getMessage() // Optional: for debugging, remove in production
            ], 500);
        }
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
