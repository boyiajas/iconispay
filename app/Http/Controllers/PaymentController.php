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
        $validated = $request->validate([
            'category' => 'required|integer',
            'account' => 'nullable|string|max:255',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'my_reference' => 'required|string|max:255',
            'recipient_reference' => 'nullable|string|max:255',
            'account_number' => 'nullable|string',
            'account_holder' => 'nullable|string',
            'id_number' => 'nullable|string',
            'account_type' => 'nullable|string',
            'institution' => 'nullable|string',
            'branch_code' => 'nullable|string'
        ]);

        // Create or update the payment logic here...

        return response()->json([
            'message' => 'Payment created successfully',
            'data' => $validated
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        //
    }
}
