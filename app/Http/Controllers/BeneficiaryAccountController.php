<?php

namespace App\Http\Controllers;

use App\Models\AccountType;
use App\Models\Authorizer;
use App\Models\BeneficiaryAccount;
use App\Models\Category;
use App\Models\FirmAccount;
use App\Models\Institution;
use App\Models\Payment;
use DataTables;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class BeneficiaryAccountController extends Controller
{
    public function indexBackup()
    {
         // Get the FirmAccount data with related 'institution'
         $beneficiaryAccounts = BeneficiaryAccount::with('institution','category','accountType')
            ->where(function ($query) {
                $query->whereHas('authorizers') // Ensure at least one authorizer exists
                    ->orWhere('verified', '>', 0); // OR ensure verified is greater than zero
            })
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

    public function index()
    {
        // Retrieve only beneficiary accounts that have at least one authorizer OR are verified
        $beneficiaryAccounts = BeneficiaryAccount::with('institution', 'category', 'accountType','authorizers.user')
            ->where(function ($query) {
                $query->where('number_of_authorizer', '<>', null) // Ensure at least one authorizer exists
                    ->orWhere('verified', '>', 0); // OR ensure verified is greater than zero
            })
            /* ->where(function ($query) {
                $query->whereHas('authorizers') // Ensure at least one authorizer exists
                    ->orWhere('verified', '>', 0); // OR ensure verified is greater than zero
            }) */
            ->get()
            ->map(function ($beneficiaryAccount) {
                // Count the number of authorizers
                $authorizerCount = $beneficiaryAccount->authorizers()->count();
                $numberOfAuthorizer = $beneficiaryAccount->number_of_authorizer ?? 0;

                if (!$beneficiaryAccount->authorised) {
                    if ($authorizerCount > 0 && $authorizerCount == $numberOfAuthorizer) {
                        // If authorizer count matches required, mark as authorised
                        $beneficiaryAccount->authorised = 1;
                        $beneficiaryAccount->save();
                    } else {
                        // Otherwise, set a progress indicator
                        $beneficiaryAccount->authorizer_progress = "$authorizerCount of $numberOfAuthorizer";
                    }
                } else {
                    // If already authorized, set progress as complete
                    $beneficiaryAccount->authorizer_progress = "$authorizerCount of $numberOfAuthorizer";
                }

                return $beneficiaryAccount;
            });

        // Return the data formatted for DataTables
        return DataTables::of($beneficiaryAccounts)->make(true);
    }

    public function getOnceOffAccounts()
    {
        // Retrieve only beneficiary accounts that have at least one authorizer OR are verified
        $onceoffAccounts = BeneficiaryAccount::with('institution', 'category', 'accountType')
            ->where(function ($query) {
                $query->where('number_of_authorizer', null) // Ensure at least one authorizer exists
                    ->orWhere('verified', 0); // OR ensure verified is greater than zero
            })
            ->get()
            ->map(function ($onceoffAccounts) {
                // Count the number of authorizers
                $authorizerCount = $onceoffAccounts->authorizers()->count();
                $numberOfAuthorizer = $onceoffAccounts->number_of_authorizer ?? 0;

                if (!$onceoffAccounts->authorised) {
                    if ($authorizerCount > 0 && $authorizerCount == $numberOfAuthorizer) {
                        // If authorizer count matches required, mark as authorised
                        $onceoffAccounts->authorised = 1;
                        $onceoffAccounts->save();
                    } else {
                        // Otherwise, set a progress indicator
                        $onceoffAccounts->authorizer_progress = "$authorizerCount of $numberOfAuthorizer";
                    }
                } else {
                    // If already authorized, set progress as complete
                    $onceoffAccounts->authorizer_progress = "$authorizerCount of $numberOfAuthorizer";
                }

                return $onceoffAccounts;
            });

        // Return the data formatted for DataTables
        return DataTables::of($onceoffAccounts)->make(true);
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
           /*  $beneficiaryAccount->load('authorizers.user', 'institution', 'accountType','category');
            //return response()->json($beneficiaryAccount->load('institution','deposits.user','accountType','payments.user','payments.accountType', 'payments.institution'));

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

            return response()->json($response); */
            return response()->json($beneficiaryAccount->load([
                'authorizers.user',
                'institution',
                'accountType',
                'category',/*
                'payments' => function ($query) {
                    $query->with('requisition', 'payToFirmAccount', 'beneficiaryAccount');
                } */
            ]));

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

    public function importBeneficiaryAccounts(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv|max:2048',
        ]);

        $file = $request->file('file');
        $data = Excel::toArray([], $file)[0]; // Read the first sheet of the file

        $importedAccounts = [];
        $errors = [];

        foreach ($data as $index => $row) {
            if ($index === 0) continue; // Skip header row

            $validator = Validator::make([
                'displayText' => $row[0] ?? null,
                'accountHolder' => $row[2] ?? null,
                'accountNumber' => $row[3] ?? null,
                'accountCategory' => $row[1] ?? null,
                'accountType' => $row[6] ?? null,
                'institution' => $row[4] ?? null,
                'branchCode' => $row[5] ?? null,
                'initials' => $row[7] ?? null,
                'surname' => $row[8] ?? null,
                'companyName' => $row[9] ?? null,
                'idNumber' => $row[10] ?? null,
                'registrationNumber' => $row[11] ?? null,
                'myReference' => $row[12] ?? null,
                'recipientReference' => $row[13] ?? null,
                'verified' => filter_var($row[14] ?? false, FILTER_VALIDATE_BOOLEAN),
                'number_of_authorizer' => $row[15] ?? null,
            ], [
                'displayText' => 'required|string|max:255',
                'accountHolder' => 'required|string|max:255',
                //'accountHolderType' => 'required|in:natural,juristic',
                'accountNumber' => 'required|string|max:50|unique:beneficiary_accounts,account_number',
                'accountCategory' => 'required|integer',
                'accountType' => 'required|string|max:50',
                'institution' => 'required|string|max:50',
                'branchCode' => 'nullable|string|max:10',
                'initials' => 'nullable|string|max:10',
                'surname' => 'nullable|string|max:100',
                'companyName' => 'nullable|string|max:255',
                'idNumber' => 'nullable|string|max:50',
                'registrationNumber' => 'nullable|string|max:100',
                'myReference' => 'nullable|string|max:100',
                'recipientReference' => 'nullable|string|max:100',
                'verified' => 'boolean',
                'number_of_authorizer' => 'nullable|integer',
            ]);

            // Extract values from row using consistent indexing
            $displayText = $row[0] ?? null;
            $accountHolder = $row[2] ?? null;
            $accountNumber = $row[3] ?? null;
            $accountCategory = strtolower(trim($row[1] ?? ''));
            $accountType = strtolower(trim($row[6] ?? ''));
            $institution = strtolower(trim($row[4] ?? ''));
            $branchCode = $row[5] ?? null;
            $initials = $row[7] ?? null;
            $surname = $row[8] ?? null;
            $companyName = $row[9] ?? null;
            $idNumber = $row[10] ?? null;
            $registrationNumber = $row[11] ?? null;
            $myReference = $row[12] ?? null;
            $recipientReference = $row[13] ?? null;
            $verified = filter_var($row[14] ?? false, FILTER_VALIDATE_BOOLEAN);
            $number_of_authorizer = $row[15] ?? null;
            $accountHolderType = null;

            // Determine `account_holder_type`
            if (empty($initials) && empty($surname)) {
                $accountHolderType = 'juristic';
            } else {
                $accountHolderType = 'natural';
            }

            // Search for IDs
            $categoryId = Category::whereRaw("LOWER(name) LIKE ?", ['%' . strtolower($accountCategory) . '%'])->value('id');
            $accountTypeId = AccountType::whereRaw("LOWER(name) LIKE ?", ['%' . strtolower($accountType) . '%'])->value('id');
            $institutionId = Institution::whereRaw("LOWER(short_name) LIKE ?", ['%' . strtolower($institution) . '%'])->value('id');

            if (!$categoryId || !$institutionId) {
                $errors[] = [
                    'row' => $index + 1,
                    'errors' => "Invalid category, account type, or institution for '$displayText'.",
                ];
                continue; // Skip invalid rows
            }

            // Check if the account number already exists
            if (BeneficiaryAccount::where('account_number', $accountNumber)->exists()) {
                $errors[] = [
                    'row' => $index + 1,
                    'errors' => "Account number '$accountNumber' already exists.",
                ];
                continue;
            }

            if ($validator->fails()) {
                $errors[] = [
                    'row' => $index + 1,
                    'errors' => $validator->errors()->all(),
                ];
                continue; // Skip invalid rows
            }

            // Create a new firm account
            $beneficiaryAccount = BeneficiaryAccount::create([
                'display_text' => $displayText,
                'category_id' => $categoryId,
                'account_holder' => $accountHolder,
                'account_holder_type' => $accountHolderType,
                'account_number' => $accountNumber,
                'account_type_id' => $accountTypeId,
                'institution_id' => $institutionId,
                'branch_code' => $branchCode,
                'initials' => $initials,
                'surname' => $surname,
                'company_name' => $companyName,
                'id_number' => $idNumber,
                'registration_number' => $registrationNumber,
                'my_reference' => $myReference,
                'recipient_reference' => $recipientReference,
                'verified' => $verified,
                'number_of_authorizer' => $number_of_authorizer,
                'user_id' => auth()->id(),
            ]);

            $importedAccounts[] = $beneficiaryAccount;
        }

        return response()->json([
            'message' => 'Firm accounts imported successfully!',
            'imported_accounts' => $importedAccounts,
            'errors' => $errors,
        ]);
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

    public function update(Request $request, BeneficiaryAccount $beneficiaryAccount)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'display_text' => 'required|string|max:255',
            'account_holder_type' => 'required|in:natural,juristic',
            'account_number' => 'required|string|max:50',
            'category_id' => 'required|integer',
            'account_type_id' => 'required|exists:account_types,id',
            'institution_id' => 'required|exists:institutions,id',
            'branch_code' => 'nullable|string|max:20',
            'initials' => 'nullable|string|max:10',
            'surname' => 'nullable|string|max:100',
            'company_name' => 'nullable|string|max:255',
            'id_number' => 'nullable|string|max:50',
            'registration_number' => 'nullable|string|max:100',
            'my_reference' => 'nullable|string|max:100',
            'recipient_reference' => 'nullable|string|max:100',
            'verified' => 'boolean',
            'number_of_authorizer' => 'nullable|integer',
            'email_address' => 'nullable|string|email|max:255',
            'phone_number' => 'nullable|string|max:20',
        ]);

        // Check if the accountNumber already exists but exclude the current record
        $existingAccount = BeneficiaryAccount::where('account_number', $request->input('account_number'))
                                            ->where('id', '!=', $beneficiaryAccount->id)
                                            ->first();
        if ($existingAccount) {
            return response()->json([
                'message' => 'The account number already exists.'
            ], 400);
        }

        // Update the BeneficiaryAccount
        $beneficiaryAccount->update([
            'display_text' => $request->input('display_text'),
            'category_id' => $request->input('category_id'),
            'account_holder_type' => $request->input('account_holder_type'),
            'account_holder' => $request->input('display_text'),
            'account_number' => $request->input('account_number'),
            'account_type_id' => $request->input('account_type_id'),
            'institution_id' => $request->input('institution_id'),
            'branch_code' => $request->input('branch_code'),
            'initials' => $request->input('initials'),
            'surname' => $request->input('surname'),
            'company_name' => $request->input('company_name'),
            'id_number' => $request->input('id_number'),
            'registration_number' => $request->input('registration_number'),
            'my_reference' => $request->input('my_reference'),
            'recipient_reference' => $request->input('recipient_reference'),
            'verified' => $request->input('verified'),
            'number_of_authorizer' => $request->input('number_of_authorizer'),
            //'user_id' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Beneficiary Account updated successfully!',
            'beneficiaryAccount' => $beneficiaryAccount
        ], 200);
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
