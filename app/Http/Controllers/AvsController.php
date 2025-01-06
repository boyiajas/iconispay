<?php

namespace App\Http\Controllers;

use App\Models\Avs;
use App\Models\BeneficiaryAccount;
use \AvsHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AvsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function verify(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'account_number' => 'required|string',
            'branch_code' => 'required|string|max:6',
            'account_holder_type' => 'required|in:natural,juristic',
            'initials' => 'nullable|string|max:5', // Only for natural persons
            'surname' => 'nullable|string|max:255', // Only for natural persons
            'company_name' => 'nullable|string|max:255', // Only for juristic persons
            'registration_number' => 'nullable|string|max:20', // Only for juristic persons
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Find the beneficiary account
        $beneficiaryAccount = BeneficiaryAccount::where('account_number', $request->account_number)
            ->where('branch_code', $request->branch_code)
            ->first();

        if (!$beneficiaryAccount) {
            return response()->json(['error' => 'Beneficiary account not found.'], 404);
        }

        // Simulate AVS response based on the provided data
        //$avsResult = $this->simulateAvs($request, $beneficiaryAccount);

        $accountTypeMapping = [
            'Unknown' => '00',
            'Cheque' => '01',
            'Savings' => '02',
            'Transmission' => '03',
            'Bond' => '04',
            'Credit card' => '05',
            'Subscription' => '06',
            'Trust' => '07',
            'Attorneys' => '08',
        ];
   
        $request_data = [
            "operator" => "AutTstOp",
            "accountNumber" => $beneficiaryAccount->account_number,
            "accountType" => $this->getBackendAccountType($beneficiaryAccount, $accountTypeMapping),//e.g. 01 -Current/cheque, 02 -savings, 03 -transmission, 04 - bond, 06 - subscription, 00 - if this is not known
            "branchCode" => $beneficiaryAccount->branch_code,
            "initials" => $beneficiaryAccount->initials,
            "idNumber" => $beneficiaryAccount->id_number ?? $beneficiaryAccount->registration_number,
            "lastName" => $beneficiaryAccount->surname,
            "phoneNumber" => "",
            "emailAddress" => "test@test.co.za",
            "userReference" => "AVS000001"
        ];

        // Use the helper class to send the request
        $avsResult = AvsHelper::send_request($request_data);

        // Handle the response
        if ($avsResult['status_code'] === 200) {
            $response_body = json_decode($avsResult['body'], true);
            return response()->json([
                "success" => true,
                "data" => $response_body,
            ]);

        }else{
            return response()->json([
                'message' => 'Unable to verify account. '.json_encode($avsResult)
            ], 405);
        }


        // Update beneficiary account with AVS result
        /* $beneficiaryAccount->verified = $avsResult['verified'];
        $beneficiaryAccount->verification_status = $avsResult['verification_status'];
        $beneficiaryAccount->account_found = $avsResult['account_found'];
        $beneficiaryAccount->account_open = $avsResult['account_open'];
        $beneficiaryAccount->account_type_verified = $avsResult['account_type_verified'];
        $beneficiaryAccount->account_type_match = $avsResult['account_type_match'];
        $beneficiaryAccount->branch_code_match = $avsResult['branch_code_match'];
        $beneficiaryAccount->holder_name_match = $avsResult['holder_name_match'];
        $beneficiaryAccount->holder_initials_match = $avsResult['holder_initials_match'];
        $beneficiaryAccount->registration_number_match = $avsResult['registration_number_match'];
        $beneficiaryAccount->avs_verified_at = now();
        $beneficiaryAccount->save();

        // Return the AVS result as a response
        return response()->json($beneficiaryAccount); */
    }

    private function getBackendAccountType(BeneficiaryAccount $beneficiaryAccount, array $accountTypeMapping)
    {
        // Retrieve the name of the account type from the relationship
        $accountTypeName = $beneficiaryAccount->accountType->name ?? 'Unknown';
    
        // Check if the account type name is contained within the mapping keys
        foreach ($accountTypeMapping as $key => $value) {
            if (Str::contains(strtolower($accountTypeName), strtolower($key))) {
                return $value;
            }
        }
    
        // Default to '00' (Unknown) if no match is found
        return '00';
    }

    private function simulateAvs($request, $beneficiaryAccount)
    {
        // Base AVS result structure
        $avsResult = [
            'verified' => true,
            'verification_status' => 'successful',
            'account_found' => true,
            'account_open' => true,
            'account_type_verified' => $beneficiaryAccount->account_type_id == 2 ? 'Cheque account' : 'Savings account',
            'account_type_match' => true,
            'branch_code_match' => $beneficiaryAccount->branch_code == $request->branch_code,
            'holder_name_match' => false,
            'holder_initials_match' => false,
            'registration_number_match' => false,
        ];

        // Differentiate between natural and juristic account verification
        if ($beneficiaryAccount->account_holder_type == 'natural') {
            // For natural persons, compare initials and surname
            $avsResult['holder_name_match'] = strtolower($beneficiaryAccount->surname) == strtolower($request->surname);
            $avsResult['holder_initials_match'] = strtolower($beneficiaryAccount->initials) == strtolower($request->initials);
        } else {
            // For juristic persons, compare company name and registration number
            $avsResult['holder_name_match'] = strtolower($beneficiaryAccount->company_name) == strtolower($request->company_name);
            $avsResult['registration_number_match'] = strtolower($beneficiaryAccount->registration_number) == strtolower($request->registration_number);
        }

        // Simulate failure scenarios
        if (!$avsResult['holder_name_match']) {
            $avsResult['verified'] = false;
            $avsResult['verification_status'] = 'failed';
        }

        return $avsResult;
    }

    private function avsVerification($request, $beneficiaryAccount)
    {
        // Base AVS result structure
        $avsResult = [
            'verified' => true,
            'verification_status' => 'successful',
            'account_found' => true,
            'account_open' => true,
            'account_type_verified' => $beneficiaryAccount->account_type_id == 2 ? 'Cheque account' : 'Savings account',
            'account_type_match' => true,
            'branch_code_match' => $beneficiaryAccount->branch_code == $request->branch_code,
            'holder_name_match' => false,
            'holder_initials_match' => false,
            'registration_number_match' => false,
        ];

        // Differentiate between natural and juristic account verification
        if ($beneficiaryAccount->account_holder_type == 'natural') {
            // For natural persons, compare initials and surname
            $avsResult['holder_name_match'] = strtolower($beneficiaryAccount->surname) == strtolower($request->surname);
            $avsResult['holder_initials_match'] = strtolower($beneficiaryAccount->initials) == strtolower($request->initials);
        } else {
            // For juristic persons, compare company name and registration number
            $avsResult['holder_name_match'] = strtolower($beneficiaryAccount->company_name) == strtolower($request->company_name);
            $avsResult['registration_number_match'] = strtolower($beneficiaryAccount->registration_number) == strtolower($request->registration_number);
        }

        // Simulate failure scenarios
        if (!$avsResult['holder_name_match']) {
            $avsResult['verified'] = false;
            $avsResult['verification_status'] = 'failed';
        }

        return $avsResult;
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Avs $avs)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Avs $avs)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Avs $avs)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Avs $avs)
    {
        //
    }
}
