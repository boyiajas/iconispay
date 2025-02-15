<?php

namespace App\Http\Controllers;

use App\Models\AccountType;
use App\Models\Authorizer;
use App\Models\BeneficiaryAccount;
use App\Models\Category;
use App\Models\FirmAccount;
use App\Models\Institution;
use App\Models\Payment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class BeneficiaryAccountController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Retrieve only beneficiary accounts that have at least one authorizer OR are verified
        $query = $user->hasRole('superadmin') 
                        ? BeneficiaryAccount::with('institution', 'category', 'accountType', 'organisation','authorizers.user')
                        : BeneficiaryAccount::whereOrganisationId($user->organisation->id)->with('institution', 'category', 'accountType', 'organisation','authorizers.user');

        // Apply filtering for accounts with at least one authorizer OR verified > 0
            $beneficiaryAccounts = $query->where(function ($q) {
                $q->whereNotNull('number_of_authorizer')
                ->orWhere('verified', '>', 0);
            })// Retrieve results
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
        $user = Auth::user();
        // Retrieve only beneficiary accounts that have at least one authorizer OR are verified
         // Retrieve only beneficiary accounts that have at least one authorizer OR are verified
         $query = $user->hasRole('superadmin') 
                    ? BeneficiaryAccount::with('institution', 'category', 'organisation', 'accountType')
                    : BeneficiaryAccount::whereOrganisationId($user->organisation->id)->with('institution', 'category', 'organisation', 'accountType');

            $onceoffAccounts = $query->where(function ($q) {
                $q->whereNull('number_of_authorizer') // Ensure at least one authorizer exists
                    ->orWhereNull('number_of_authorizer')->Where('verified', 0); // OR ensure verified is greater than zero
            })
            ->get()
            ->map(function ($onceoffAccounts) {
                // Count the number of authorizers
                $authorizerCount = $onceoffAccounts->authorizers()->count();
                $numberOfAuthorizer = $onceoffAccounts->number_of_authorizer ?? 0;

                if (!$onceoffAccounts->authorised) {
                    if ($authorizerCount > 0 && $authorizerCount == $numberOfAuthorizer) {
                        // If authorizer count matches required, mark as authorised
                        //$onceoffAccounts->authorised = 1;
                        //$onceoffAccounts->save();
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
            $user = Auth::user();
            $isSuperAdmin = $user->hasRole('superadmin');

            // Search for Beneficiary Account based on role
            $query = $isSuperAdmin
                ? BeneficiaryAccount::whereId($beneficiaryId)
                : BeneficiaryAccount::whereOrganisationId($user->organisation->id)->whereId($beneficiaryId);

            $beneficiaryAccount = $query->whereAccountNumber($accountNumber)
                ->with('authorizers.user', 'institution', 'accountType', 'category')
                ->first();

            // If BeneficiaryAccount not found, search for FirmAccount
            if (!$beneficiaryAccount) {
                $query = $isSuperAdmin
                    ? FirmAccount::whereId($beneficiaryId)
                    : FirmAccount::whereOrganisationId($user->organisation->id)->whereId($beneficiaryId);

                $beneficiaryAccount = $query->whereAccountNumber($accountNumber)
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
            \Log::error('Error fetching account details: ' . $e->getMessage());

            return response()->json([
                'message' => 'An error occurred while retrieving the account details.',
                'error' => config('app.debug') ? $e->getMessage() : null // Hide error in production
            ], 500);
        }
    }


    public function show(BeneficiaryAccount $beneficiaryAccount)
    {
        
        try {
            // Load the necessary relationships and format the response data
           
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
                'organisation_id' => $request->organisation_id ? $request->organisation_id : Auth::user()->organisation->id,
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
            'organisation_id' => Auth::user()->organisation->id
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
            'organisation_id' => Auth::user()->organisation->id
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
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('superadmin');

        try {
            // Beneficiary Account Query
            $beneficiaryQuery = $isSuperAdmin
                ? BeneficiaryAccount::with('institution', 'category', 'accountType', 'payments', 'authorizers.user')
                : BeneficiaryAccount::whereOrganisationId($user->organisation->id)
                    ->with('institution', 'category', 'accountType', 'payments', 'authorizers.user');

            $beneficiaries = $beneficiaryQuery->where(function ($q) use ($query) {
                $q->where('account_number', 'LIKE', "%{$query}%")
                ->orWhere('company_name', 'LIKE', "%{$query}%")
                ->orWhere('display_text', 'LIKE', "%{$query}%");
            })->take(10)->get();

            // Firm Account Query
            $firmQuery = $isSuperAdmin
                ? FirmAccount::with('institution', 'category', 'accountType', 'payments', 'authorizers.user')
                : FirmAccount::whereOrganisationId($user->organisation->id)
                    ->with('institution', 'category', 'accountType', 'payments', 'authorizers.user');

            $firmAccounts = $firmQuery->where(function ($q) use ($query) {
                $q->where('account_number', 'LIKE', "%{$query}%")
                ->orWhere('account_holder', 'LIKE', "%{$query}%")
                ->orWhere('company_name', 'LIKE', "%{$query}%")
                ->orWhere('display_text', 'LIKE', "%{$query}%");
            })->take(10)->get();

            // Merge the results
            $results = $beneficiaries->merge($firmAccounts);

            // Check if results are empty
            if ($results->isEmpty()) {
                return response()->json(['message' => 'No records found'], 202);
            }

            // Return the results as JSON
            return response()->json($results);

        } catch (\Exception $e) {
            \Log::error('Error searching accounts: ' . $e->getMessage());

            return response()->json([
                'message' => 'An error occurred while searching for accounts.',
                'error' => config('app.debug') ? $e->getMessage() : null // Hide error in production
            ], 500);
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
