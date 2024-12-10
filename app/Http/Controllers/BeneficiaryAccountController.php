<?php

namespace App\Http\Controllers;

use App\Models\Authorizer;
use App\Models\BeneficiaryAccount;
use App\Models\FirmAccount;
use App\Models\Payment;
use DataTables;
use Exception;
use Illuminate\Http\Request;

class BeneficiaryAccountController extends Controller
{
    public function index()
    {
         // Get the FirmAccount data with related 'institution'
         $beneficiaryAccounts = BeneficiaryAccount::with('institution','category','accountType')
         ->get()
        ->map(function ($beneficiaryAccount) {
            // Check if the firm account has not been authorized
            if (!$beneficiaryAccount->authorised ) {
                // Count the number of entries in the Authorizer model for this firm account
                $authorizerCount = $beneficiaryAccount->authorizers()->count() ?? 0;
                $numberOfAuthorizer = $beneficiaryAccount->number_of_authorizer ?? 0;

                if ($authorizerCount > 0 && $authorizerCount == $numberOfAuthorizer) {
                    // If the count matches, set 'authorised' to 1
                    $beneficiaryAccount->authorised = 1;
                    $beneficiaryAccount->save();
                } else {
                    // Otherwise, set a custom property to indicate the authorizer progress
                    $beneficiaryAccount->authorizer_progress = "$authorizerCount of $numberOfAuthorizer";
                }
            } else {
                // Count the number of entries in the Authorizer model for this firm account
                $authorizerCount = $beneficiaryAccount->authorizers()->count() ?? 0;
                // If already authorized, set authorizer progress as complete
                $beneficiaryAccount->authorizer_progress = ($authorizerCount) ." of ".($beneficiaryAccount->number_of_authorizer ?? 0);
            }

            return $beneficiaryAccount;
        });

         // Use the DataTables facade to return data in the required format
         return DataTables::of($beneficiaryAccounts)->make(true);
    }

    public function showBeneficiaryAndFirm($beneficiaryId, $accountNumber)
    {
        try {
            // Search for the BeneficiaryAccount or FirmAccount using the ID and account number
            $beneficiaryAccount = BeneficiaryAccount::whereId($beneficiaryId)
                ->whereAccountNumber($accountNumber)
                ->with('authorizers.user', 'institution', 'accountType', 'category')
                ->first();
    
            if (!$beneficiaryAccount) {
                $beneficiaryAccount = FirmAccount::whereId($beneficiaryId)
                    ->whereAccountNumber($accountNumber)
                    ->with('authorizers.user', 'institution', 'accountType', 'category')
                    ->first();
            }
    
            if (!$beneficiaryAccount) {
                return response()->json(['message' => 'Account not found'], 404);
            }
    
            // Construct the response data
            $response = [
                'id' => $beneficiaryAccount->id,
                'account_number' => $beneficiaryAccount->account_number,
                'company_name' => $beneficiaryAccount->company_name,
                'initials' => $beneficiaryAccount->initials,
                'surname' => $beneficiaryAccount->surname,
                'id_number' => $beneficiaryAccount->id_number,
                'registration_number' => $beneficiaryAccount->registration_number,
                'verified' => $beneficiaryAccount->verified,
                'payments' => $beneficiaryAccount->payments,
                'category' => $beneficiaryAccount->category,
                'account_type' => $beneficiaryAccount->accountType,
                'institution' => $beneficiaryAccount->institution,
                'branch_code' => $beneficiaryAccount->branch_code,
                'authorizers' => $beneficiaryAccount->authorizers,
                'account_holder_type' => $beneficiaryAccount->account_holder_type,
                'authorised' => $beneficiaryAccount->authorised,
                'display_text' => $beneficiaryAccount->display_text,
                'authorizersDetails' => $beneficiaryAccount->getAuthorizerDetails(),
            ];
    
            return response()->json($response);
    
        } catch (\Exception $e) {
            \Log::error('Error getting requisition: ' . $e->getMessage());
    
            return response()->json([
                'message' => 'An error occurred while getting requisition.',
                'error' => $e->getMessage() // Optional: for debugging, remove in production
            ], 500);
        }
    }

    public function show(BeneficiaryAccount $beneficiaryAccount)
    {
        
        try {
            // Load the necessary relationships and format the response data
            $beneficiaryAccount->load('authorizers.user', 'institution', 'accountType','category');

            // Construct the response data to include all required fields for the frontend
            $response = [
                'id' => $beneficiaryAccount->id,
                'account_number' => $beneficiaryAccount->account_number,
                'company_name' => $beneficiaryAccount->company_name,
                'initials' => $beneficiaryAccount->initials,
                'surname' => $beneficiaryAccount->surname,
                'id_number' => $beneficiaryAccount->id_number,
                'registration_number' => $beneficiaryAccount->registration_number,
                'verified' => $beneficiaryAccount->verified,
                'payments' => $beneficiaryAccount->payments, // Assuming payments is a field or relationship
                'category' => $beneficiaryAccount->category,
                'account_type' => $beneficiaryAccount->accountType, // Include account type details
                'institution' => $beneficiaryAccount->institution, // Include institution details
                'branch_code' => $beneficiaryAccount->branch_code,
                'authorizers' => $beneficiaryAccount->authorizers, // Include authorizer details if needed
                'account_holder_type' => $beneficiaryAccount->account_holder_type,
                'authorised' => $beneficiaryAccount->authorised,
                'display_text' => $beneficiaryAccount->display_text,
                'authorizersDetails' => $beneficiaryAccount->getAuthorizerDetails(),
            ];

            return response()->json($response);

        } catch (\Exception $e) {
            // Log the error message
            \Log::error('Error getting requisition: ' . $e->getMessage());

            // Return an error response to the client
            return response()->json([
                'message' => 'An error occurred while getting requisition.',
                'error' => $e->getMessage() // Optional: for debugging, remove in production
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'displayText' => 'required|string|max:255',
            'accountCategory' => 'required|integer',
            'accountNumber' => 'required|string|max:255',
            'accountHolderType' => 'required|string|max:255',
            'accountType' => 'required|integer',
            'institution' => 'required|exists:institutions,id',
            'branchCode' => 'required|string|max:255',
            'initials' => 'nullable|string|max:10',
            'surname' => 'nullable|string|max:100',
            'companyName' => 'nullable|string|max:255',
            'idNumber' => 'nullable|string|max:50',
            'registrationNumber' => 'nullable|string|max:100',
            'myReference' => 'nullable|string|max:100',
            'recipientReference' => 'nullable|string|max:100',
            'verified' => 'boolean',
            'numberOfAuthorizer' => 'nullable|integer',
            'emailAddress' => 'nullable|string|email|max:255',
            'phoneNumber' => 'nullable|string|max:20',
        ]);

        // Check if the accountNumber already exists
        $existingAccount = BeneficiaryAccount::where('account_number', $request->input('accountNumber'))->first();
        if ($existingAccount) {
            return response()->json([
                'message' => 'The account number already exists.'
            ], 400);
        }

         // Create the FirmAccount
         $beneficiaryAccount = BeneficiaryAccount::create([
            'display_text' => $request->input('displayText'),
            'category_id' => $request->input('accountCategory'),
            'account_holder_type' => $request->input('accountHolderType'),
            'account_holder' => $request->input('displayText'),
            'account_number' => $request->input('accountNumber'),
            'account_type_id' => $request->input('accountType'),
            'institution_id' => $request->input('institution.id'),
            'branch_code' => $request->input('branchCode'),
            'initials' => $request->input('initials'),
            'surname' => $request->input('surname'),
            'company_name' => $request->input('companyName'),
            'id_number' => $request->input('idNumber'),
            'registration_number' => $request->input('registrationNumber'),
            'my_reference' => $request->input('myReference'),
            'recipient_reference' => $request->input('recipientReference'),
            'verified' => $request->input('verified'),
            //'authorised' => $request->input('verified'),
            'number_of_authorizer' => $request->input('numberOfAuthorizer'),
            'user_id' => auth()->id(),
        ]);

        //$beneficiaryAccount = BeneficiaryAccount::create($validated);

        return response()->json($beneficiaryAccount, 201);
    }

    public function authorise(Request $request, $sourceAccountId)
    {
        // Find the beneficiaryAccount by ID
        $beneficiaryAccount = BeneficiaryAccount::findOrFail($sourceAccountId);

        // Check if the firm account has already been authorized
        if ($beneficiaryAccount->authorised) {
            return response()->json([
                'message' => 'This firm account has already been authorised.'
            ], 201);
        }

        // Check if the user has already authorized this firm account
        $userId = $request->user()->id; // Assuming the user is authenticated and you have their ID
        $existingAuthorizer = Authorizer::where('beneficiary_account_id', $beneficiaryAccount->id)
            ->where('user_id', $userId)
            ->first();

        if ($existingAuthorizer) {
            return response()->json([
                'message' => 'You have already authorized this beneficiary account.'
            ], 201);
        }

        // Create a new Authorizer record
        Authorizer::create([
            'firm_account_id' => null,
            'beneficiary_account_id' => $beneficiaryAccount->id, // You can set this if applicable
            'user_id' => $userId,
        ]);

        // Count the number of authorizer entries for this firm account
        $authorizerCount = $beneficiaryAccount->authorizers()->count();
        $numberOfAuthorizer = $beneficiaryAccount->number_of_authorizer;

        // Check if the current number of authorizers meets the required number
        if ($authorizerCount >= $numberOfAuthorizer) {
            // Mark the firm account as authorised
            $beneficiaryAccount->authorised = 1;
            $beneficiaryAccount->save();

            return response()->json([
                'message' => 'Firm account has been successfully authorised.',
                'authoriser_count' => $authorizerCount,
                'required_count' => $numberOfAuthorizer
            ], 200);
        }

        // If not enough authorizers, return the current progress
        return response()->json([
            'message' => 'Not enough authorizers to authorise this firm account.',
            'authoriser_count' => $authorizerCount,
            'required_count' => $numberOfAuthorizer
        ], 201);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');

        try {
            // Query beneficiaries based on the account number or account holder name
            $beneficiaries = BeneficiaryAccount::where('account_number', 'LIKE', "%{$query}%")
            ->orWhere('company_name', 'LIKE', "%{$query}%")
            ->orWhere('display_text', 'LIKE', "%{$query}%")->with('institution','category','accountType','payments','authorizers.user')
            ->take(10)  // Limit to 10 results
            //->get(['account_number', 'company_name', 'display_text', 'id', 'institution_id']);  // Only return relevant fields
            ->get();

            // Query FirmAccount based on account number or account holder name
            $firmAccounts = FirmAccount::where('account_number', 'LIKE', "%{$query}%")
                ->orWhere('account_holder', 'LIKE', "%{$query}%")
                ->orWhere('company_name', 'LIKE', "%{$query}%")
                ->orWhere('display_text', 'LIKE', "%{$query}%")
                ->with('institution', 'category', 'accountType','payments','authorizers.user')
                ->take(10) // Limit to 10 results
                ->get();

                // Combine the results from both queries
            $results = $beneficiaries->merge($firmAccounts);
            
            // Check if results are empty
            if ($results->isEmpty()) {
                return response()->json(['message' => 'No records found'], 202);
            }

            // Return the results as JSON
            return response()->json($results);

        } catch (Exception $e) {
            // Return an error response if no results are found
            return response()->json(['message' => $e->getMessage()], 202);
        }
    }

    public function destroy(BeneficiaryAccount $beneficiaryAccount)
    {
        // Delete all payments associated with this beneficiary account
        Payment::whereBeneficiaryAccountId($beneficiaryAccount->id)->delete();

        $beneficiaryAccount->delete();
        return response()->json(['message' => 'Beneficiary Account deleted successfully.']);
    }
}
