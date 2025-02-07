<?php

namespace App\Http\Controllers;

use \AvsHelper;
use App\Models\Avs;
use App\Models\BeneficiaryAccount;
use App\Models\FirmAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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
            'id_number' => 'nullable|string|max:20', // Only for juristic persons
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

       /*  // Find the beneficiary account
        $beneficiaryAccount = BeneficiaryAccount::where('account_number', $request->account_number)
            ->where('branch_code', $request->branch_code)
            ->first(); */
        
        $beneficiaryAccount = null;

        if ($request->account_number) {
            $beneficiaryAccount = BeneficiaryAccount::where('account_number', $request->account_number)
                                        ->where('branch_code', $request->branch_code)->first();
    
            if ($beneficiaryAccount) {
                $beneficiaryAccount->update([
                    'verification_status' => 'pending'
                ]);
            } else {
                $beneficiaryAccount = FirmAccount::where('account_number', $request->account_number)
                                        ->where('branch_code', $request->branch_code)->first();
    
                if ($beneficiaryAccount) {
                    $beneficiaryAccount->update([
                        'verification_status' => 'pending'
                    ]);
                }
            }
        }

        if (!$beneficiaryAccount) {
            return response()->json(['error' => 'Beneficiary account not found.'], 404);
        }

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
            "accountType" => $this->getBackendAccountType($beneficiaryAccount->account_number, $accountTypeMapping),//e.g. 01 -Current/cheque, 02 -savings, 03 -transmission, 04 - bond, 06 - subscription, 00 - if this is not known
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

        // Simulate AVS response based on the provided data
        //$avsResult = $this->avsHelperSimulation($request_data);

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
    }

    private function getBackendAccountType($accountNumber, array $accountTypeMapping)
    {
        
        $beneficiaryAccount = BeneficiaryAccount::where('account_number', $accountNumber)->first();

        if(!$beneficiaryAccount){

            $beneficiaryAccount = FirmAccount::where('account_number', $accountNumber)->first();
        }
        
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

    private function avsHelperSimulation(array $data)
    {
        $simulatedResponse = [
            "status_code" => 200,
            "body" => json_encode([
                "success" => true,
                "errmsg" => "00001 Transaction sent to the bank. Waiting for feedback",
                "count" => 1,
                "Response" => [
                    "operator" => "AutTstOp",
                    "accountNumber" => $data['accountNumber'],
                    "accountType" => $data['accountType'],
                    "branchCode" => $data['branchCode'],
                    "idNumber" => $data['idNumber'],
                    "initials" => $data['initials'] ?? "",
                    "lastName" => $data['lastName'] ?? "",
                    "phoneNumber" => "",
                    "emailAddress" => "test@test.co.za",
                    "userReference" => "AVS000001",
                    "accountExists" => "",
                    "accountIdMatch" => "",
                    "initialMatch" => "",
                    "lastNameMatch" => "",
                    "accountOpen" => "",
                    "accountAcceptsCredits" => "",
                    "accountAcceptsDebits" => "",
                    "accountOpenGtThreeMonths" => "",
                    "phoneValid" => "",
                    "emailValid" => "",
                    "accountTypeValid" => "",
                    "transactionReference" => "3530132",
                    "messageCode" => "00001",
                    "messageDescription" => "Transaction sent to the bank. Waiting for feedback"
                ]
            ])
        ];

        // Simulate callback after 30 seconds
        $callbackUrl = url('/api/avs-callback');
        $callbackData = [
          
            "Response" => [
                "operator" => "AutTstOp",
                "accountNumber" => $data['accountNumber'],
                "accountType" => $data['accountType'],
                "branchCode" => $data['branchCode'],
                "idNumber" => $data['idNumber'],
                "initials" => $data['initials'] ?? null,
                "lastName" => $data['lastName'] ?? null,
                "phoneNumber" => null,
                "emailAddress" => "test@test.co.za",
                "userReference" => "AVS000001",
                "accountExists" => "00",
                "accountIdMatch" => "00",
                "initialMatch" => "00",
                "lastNameMatch" => "00",
                "accountOpen" => "00",
                "accountAcceptsCredits" => "00",
                "accountAcceptsDebits" => "00",
                "accountOpenGtThreeMonths" => "00",
                "phoneValid" => "00",
                "emailValid" => "00",
                "accountTypeValid" => "00",
                "transactionReference" => "3529771",
                "messageCode" => "00000",
                "messageDescription" => "Successful"
            ]
               
        ];

        // Execute callback asynchronously
        if (function_exists('pcntl_fork')) {
            $pid = pcntl_fork();
            if ($pid === -1) {
                // Fork failed
                error_log('Could not fork process');
            } elseif ($pid === 0) {
                // Child process: handle callback
                sleep(10);
                Http::withBasicAuth('iconis', 'test123')->post($callbackUrl, $callbackData);
                exit(0);
            }
            // Parent process continues
        } else {
            // Fallback: non-blocking sleep and execution
            register_shutdown_function(function () use ($callbackUrl, $callbackData) {
                sleep(10);
                Http::withBasicAuth('iconis', 'test123')->post($callbackUrl, $callbackData);
            });
        }

        return $simulatedResponse;
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
