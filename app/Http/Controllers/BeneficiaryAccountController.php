<?php

namespace App\Http\Controllers;

use App\Models\BeneficiaryAccount;
use DataTables;
use Illuminate\Http\Request;

class BeneficiaryAccountController extends Controller
{
    public function index()
    {
         // Get the FirmAccount data with related 'institution'
         $beneficiaryAccount = BeneficiaryAccount::with('institution','category','accounttype');

         // Use the DataTables facade to return data in the required format
         return DataTables::of($beneficiaryAccount)->make(true);
    }

    public function show(BeneficiaryAccount $beneficiaryAccount)
    {

    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'display_text' => 'required|string|max:255',
            'category_id' => 'required|integer',
            'account_number' => 'required|string|max:255',
            'account_holder_type' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'registration_number' => 'nullable|string|max:255',
            'account_type_id' => 'required|integer',
            'institution_id' => 'required|exists:institutions,id',
            'branch_code' => 'required|string|max:255',
            'my_reference' => 'nullable|string|max:255',
            'recipient_reference' => 'nullable|string|max:255',
        ]);

        $beneficiaryAccount = BeneficiaryAccount::create($validated);

        return response()->json($beneficiaryAccount, 201);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');

        try {
            // Query beneficiaries based on the account number or account holder name
            $beneficiaries = BeneficiaryAccount::where('account_number', 'LIKE', "%{$query}%")
            ->orWhere('company_name', 'LIKE', "%{$query}%")->with('institution','category','accounttype')
            ->take(10)  // Limit to 10 results
            //->get(['account_number', 'company_name', 'display_text', 'id', 'institution_id']);  // Only return relevant fields
            ->get();
            
            // Check if results are empty
            if ($beneficiaries->isEmpty()) {
                return response()->json(['message' => 'No beneficiaries found'], 202);
            }

            // Return the results as JSON
            return response()->json($beneficiaries);

        } catch (Exception $e) {
            // Return an error response if no results are found
            return response()->json(['message' => $e->getMessage()], 202);
        }
    }
}
