<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Payment;
use App\Models\Requisition;
use App\Observers\AuditTrailObserver;
use Carbon\Carbon;
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
                 /* 'locked' => 1,
                 'locked_at' => Carbon::now(),
                 'locked_by' => auth()->id(), */
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

        AuditTrailObserver::logCustomAction('Balance Payment Fund', $deposit, null, $deposit->toArray());

        // Eager load the relationships
        $requisition->load(
            'user',
            'authorizedBy',
            'lockedBy',
            'firmAccount.institution',  
            'payments.beneficiaryAccount.institution',
            'payments.payToFirmAccount.institution',
            'payments.beneficiaryAccount.accountType',
            'payments.payToFirmAccount.accountType',
            //'payments.payToFirmAccount',
            'payments.sourceFirmAccount',
            'deposits.firmAccount',
            'deposits.user'
        );

        // Transform the requisition data to include payToAccount details
        $requisitionData = $requisition->toArray();

        // Add payToAccount details for each payment
        $requisitionData['payments'] = $requisition->payments->map(function ($payment) {
            $paymentData = $payment->toArray();

            // Get the payToAccount and include its institution details
            $payToAccount = $payment->payToAccount;
            $payToAccountData = $payToAccount ? $payToAccount->toArray() : null;

            if ($payToAccount && $payToAccount->institution) {
                $payToAccountData['institution'] = $payToAccount->institution->toArray();
            }

            if ($payToAccount && $payToAccount->accountType) {
                $payToAccountData['account_type'] = $payToAccount->accountType->toArray();
            }

            // Include the transformed payToAccount in the payment data
            $paymentData['beneficiary_account'] = $payToAccountData;
           
            return $paymentData;
        });

        return response()->json($requisitionData);

       // Eager load the user relationship and return the deposit with user data
        //$deposit->load('user');
        //return response()->json($requisition->load('user', 'authorizedBy', 'firmAccount.institution', 'payments.beneficiaryAccount', 'payments.beneficiaryAccount.institution', 'deposits.firmAccount', 'deposits.user'), 201);
        //return response()->json($requisition->load(['deposits.user']), 201); // Return the created deposit with user data

    }

    public function balancePaymentDontFund(Request $request)
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
                 'funding_status' => null, // true should be a boolean, not a string
                 'capturing_status' => 1,
                 'status_id' => 3, //chainge status to awaiting authorization 
                 'locked' => null
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

        // Eager load the relationships
        $requisition->load(
            'user',
            'authorizedBy',
            'firmAccount.institution',
            'payments.beneficiaryAccount.institution',
            'payments.payToFirmAccount.institution',
            'payments.beneficiaryAccount.accountType',
            'payments.payToFirmAccount.accountType',
            //'payments.payToFirmAccount',
            'payments.sourceFirmAccount',
            'deposits.firmAccount',
            'deposits.user'
        );

        // Transform the requisition data to include payToAccount details
        $requisitionData = $requisition->toArray();

        // Add payToAccount details for each payment
        $requisitionData['payments'] = $requisition->payments->map(function ($payment) {
            $paymentData = $payment->toArray();

            // Get the payToAccount and include its institution details
            $payToAccount = $payment->payToAccount;
            $payToAccountData = $payToAccount ? $payToAccount->toArray() : null;

            if ($payToAccount && $payToAccount->institution) {
                $payToAccountData['institution'] = $payToAccount->institution->toArray();
            }

            if ($payToAccount && $payToAccount->accountType) {
                $payToAccountData['account_type'] = $payToAccount->accountType->toArray();
            }

            // Include the transformed payToAccount in the payment data
            $paymentData['beneficiary_account'] = $payToAccountData;
           
            return $paymentData;
        });

        return response()->json($requisitionData);

       // Eager load the user relationship and return the deposit with user data
        //$deposit->load('user');
        //return response()->json($requisition->load('user', 'authorizedBy', 'firmAccount.institution', 'payments.beneficiaryAccount', 'payments.beneficiaryAccount.institution', 'deposits.firmAccount', 'deposits.user'), 201);
        //return response()->json($requisition->load(['deposits.user']), 201); // Return the created deposit with user data

    }

    public function fundDeposits(Request $request)
    {
        $request->validate([
            'requisition_id' => 'required|exists:requisitions,id'
        ]);

        $requisitionId = $request->input('requisition_id');

        // Update all deposits for this requisition to funded: true
        Deposit::where('requisition_id', $requisitionId)->update(['funded' => true]);

        // Update the requisition's funding_status to true
        $requisition = Requisition::find($requisitionId);
        $requisition->update(['funding_status' => true]);

        // Eager load the relationships
        $requisition->load(
            'user',
            'authorizedBy',
            'firmAccount.institution',
            'payments.beneficiaryAccount.institution',
            'payments.payToFirmAccount.institution',
            'payments.beneficiaryAccount.accountType',
            'payments.payToFirmAccount.accountType',
            //'payments.payToFirmAccount',
            'payments.sourceFirmAccount',
            'deposits.firmAccount',
            'deposits.user'
        );

        // Transform the requisition data to include payToAccount details
        $requisitionData = $requisition->toArray();

        // Add payToAccount details for each payment
        $requisitionData['payments'] = $requisition->payments->map(function ($payment) {
            $paymentData = $payment->toArray();

            // Get the payToAccount and include its institution details
            $payToAccount = $payment->payToAccount;
            $payToAccountData = $payToAccount ? $payToAccount->toArray() : null;

            if ($payToAccount && $payToAccount->institution) {
                $payToAccountData['institution'] = $payToAccount->institution->toArray();
            }

            if ($payToAccount && $payToAccount->accountType) {
                $payToAccountData['account_type'] = $payToAccount->accountType->toArray();
            }

            // Include the transformed payToAccount in the payment data
            $paymentData['beneficiary_account'] = $payToAccountData;
           
            return $paymentData;
        });

        return response()->json($requisitionData);
        //return response()->json($requisition->load('user', 'authorizedBy', 'firmAccount.institution', 'payments.beneficiaryAccount', 'payments.beneficiaryAccount.institution', 'deposits.firmAccount', 'deposits.user'), 201);
        //return response()->json($requisition, 200);
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
        $depositDate = $request->input('deposit_date') ? $request->input('deposit_date') : null;

         // Check if an unfunded deposit already exists for the same requisition_id
        $existingUnfundedDeposit = Deposit::where('requisition_id', $request->input('requisition_id'))
        ->where('funded', false)
        ->exists();

        //here we want to update the requisition funding status
        $requisition = Requisition::find($request->input('requisition_id'));

        if(!$existingUnfundedDeposit && $requisition){
            $requisition->update([
                'funding_status' => $request->input('funded') ? 1 : null, // true should be a boolean, not a string
            ]);
        }

        // Check if the requisition already has an existing payment
        $existingPayment = Payment::where('requisition_id', $request->input('requisition_id'))->exists();
        // If an existing payment is found, update requisition status_id to 3
        if ($existingPayment && $requisition) {
            $requisition->update(['status_id' => 3]);
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

        // Eager load the relationships
        $requisition->load(
            'user',
            'authorizedBy',
            'firmAccount.institution',
            'payments.beneficiaryAccount.institution',
            'payments.payToFirmAccount.institution',
            'payments.beneficiaryAccount.accountType',
            'payments.payToFirmAccount.accountType',
            //'payments.payToFirmAccount',
            'payments.sourceFirmAccount',
            'deposits.firmAccount',
            'deposits.user'
        );

        // Transform the requisition data to include payToAccount details
        $requisitionData = $requisition->toArray();

        // Add payToAccount details for each payment
        $requisitionData['payments'] = $requisition->payments->map(function ($payment) {
            $paymentData = $payment->toArray();

            // Get the payToAccount and include its institution details
            $payToAccount = $payment->payToAccount;
            $payToAccountData = $payToAccount ? $payToAccount->toArray() : null;

            if ($payToAccount && $payToAccount->institution) {
                $payToAccountData['institution'] = $payToAccount->institution->toArray();
            }

            if ($payToAccount && $payToAccount->accountType) {
                $payToAccountData['account_type'] = $payToAccount->accountType->toArray();
            }

            // Include the transformed payToAccount in the payment data
            $paymentData['beneficiary_account'] = $payToAccountData;
           
            return $paymentData;
        });

        return response()->json($requisitionData);

       // Eager load the user relationship and return the deposit with user data
        //$deposit->load('user');
        //return response()->json($requisition->load('user', 'authorizedBy', 'firmAccount.institution', 'payments.beneficiaryAccount', 'payments.beneficiaryAccount.institution', 'deposits.firmAccount', 'deposits.user'), 201);
        //return response()->json($deposit, 201); // Return the created deposit with user data
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

        $requisition = $deposit->requisition;

        // Eager load the relationships
        $requisition->load(
            'user',
            'authorizedBy',
            'firmAccount.institution',
            'payments.beneficiaryAccount.institution',
            'payments.payToFirmAccount.institution',
            'payments.beneficiaryAccount.accountType',
            'payments.payToFirmAccount.accountType',
            //'payments.payToFirmAccount',
            'payments.sourceFirmAccount',
            'deposits.firmAccount',
            'deposits.user'
        );

        // Transform the requisition data to include payToAccount details
        $requisitionData = $requisition->toArray();

        // Add payToAccount details for each payment
        $requisitionData['payments'] = $requisition->payments->map(function ($payment) {
            $paymentData = $payment->toArray();

            // Get the payToAccount and include its institution details
            $payToAccount = $payment->payToAccount;
            $payToAccountData = $payToAccount ? $payToAccount->toArray() : null;

            if ($payToAccount && $payToAccount->institution) {
                $payToAccountData['institution'] = $payToAccount->institution->toArray();
            }

            if ($payToAccount && $payToAccount->accountType) {
                $payToAccountData['account_type'] = $payToAccount->accountType->toArray();
            }

            // Include the transformed payToAccount in the payment data
            $paymentData['beneficiary_account'] = $payToAccountData;
           
            return $paymentData;
        });

        return response()->json($requisitionData);

       /*  // Eager load the user relationship and return the deposit with user data
        $deposit->load('user');

        return response()->json($deposit); */
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Deposit $deposit)
    {
        // Get the requisition associated with the deposit
        $requisition = $deposit->requisition;

        // Delete the deposit
        $deposit->delete();

        // Check if there are remaining deposits for the requisition
        $remainingDeposits = $requisition->deposits()->exists();

        if ($remainingDeposits) {
            // Check if any remaining deposits are not funded
            $hasUnfundedDeposit = $requisition->deposits()->where('funded', false)->exists();

            // Update funding_status based on the presence of unfunded deposits
            $requisition->update([
                'funding_status' => $hasUnfundedDeposit ? null : true,
                'capturing_status' => null,
                'status_id' => 2,
                'locked' => null,
                'authorization_status' => null,
                'authorized_user_id' => null,
                'authorized_at' => null,
            ]);
        } else {
            // If there are no remaining deposits, reset the funding_status to null
            $requisition->update([
                'funding_status' => null,
                'capturing_status' => null,
                'status_id' => 2,
                'locked' => null,
                'authorization_status' => null,
                'authorized_user_id' => null,
                'authorized_at' => null,
            ]);
        }

        // Eager load the relationships
        $requisition->load(
            'user',
            'authorizedBy',
            'firmAccount.institution',
            'payments.beneficiaryAccount.institution',
            'payments.payToFirmAccount.institution',
            'payments.beneficiaryAccount.accountType',
            'payments.payToFirmAccount.accountType',
            //'payments.payToFirmAccount',
            'payments.sourceFirmAccount',
            'deposits.firmAccount',
            'deposits.user'
        );

        // Transform the requisition data to include payToAccount details
        $requisitionData = $requisition->toArray();

        // Add payToAccount details for each payment
        $requisitionData['payments'] = $requisition->payments->map(function ($payment) {
            $paymentData = $payment->toArray();

            // Get the payToAccount and include its institution details
            $payToAccount = $payment->payToAccount;
            $payToAccountData = $payToAccount ? $payToAccount->toArray() : null;

            if ($payToAccount && $payToAccount->institution) {
                $payToAccountData['institution'] = $payToAccount->institution->toArray();
            }

            if ($payToAccount && $payToAccount->accountType) {
                $payToAccountData['account_type'] = $payToAccount->accountType->toArray();
            }

            // Include the transformed payToAccount in the payment data
            $paymentData['beneficiary_account'] = $payToAccountData;
           
            return $paymentData;
        });

        return response()->json($requisitionData);
        //return response()->json($requisition->load('user', 'authorizedBy', 'firmAccount.institution', 'payments.beneficiaryAccount', 'payments.beneficiaryAccount.institution', 'deposits.firmAccount', 'deposits.user'), 201);
        //return response()->json($requisition->load(['deposits.user']), 201);
    }

}
