<?php

namespace App\Http\Controllers;

use \AvsHelper;
use App\Models\Avs;
use App\Models\BeneficiaryAccount;
use App\Models\FirmAccount;
use App\Observers\AuditTrailObserver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AvsController extends Controller
{
    private $maxAttempts = 3; // Number of failed attempts before lockout
    private $lockoutTime = 76800; // Lockout time in seconds (1280 minutes) (21 hours)

    private $dev_username = 'iconis';
    private $dev_password = 'test123';

    private $prod_username = 'iconispay';
    private $prod_password = '1c0n1SP4Y$ON2o';
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
            "idNumber" => $beneficiaryAccount->id_number ?? preg_replace('/\D/', '', $beneficiaryAccount->registration_number),
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
                Http::withBasicAuth($this->prod_username, $this->prod_password)->post($callbackUrl, $callbackData);
                exit(0);
            }
            // Parent process continues
        } else {
            // Fallback: non-blocking sleep and execution
            register_shutdown_function(function () use ($callbackUrl, $callbackData) {
                sleep(10);
                Http::withBasicAuth($this->prod_username, $this->prod_password)->post($callbackUrl, $callbackData);
            });
        }

        return $simulatedResponse;
    }

    /**
     * Check if AVS verification is successful.
     *
     * @param array $codes
     * @return bool
     */
    private function isAvsVerificationSuccessful(array $codes): bool
    {
        return collect($codes)->every(fn($code) => $code === '00');
    }


    /**
     * Authenticate the incoming request.
     *
     * @param Request $request
     * @return bool
     */
    private function authenticateRequest(Request $request)
    {
        $username = trim($request->header('PHP_AUTH_USER'));
        $password = trim($request->header('PHP_AUTH_PW'));

        $hashedPassword = Hash::make($this->prod_password);
       
        return $username === 'iconis' && Hash::check($password, $hashedPassword);;
    }

    /**
     * Handle AVS callback.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleAvsCallback(Request $request)
    {
        $ip = $request->ip();
        $cacheKey = "avs_callback_attempts_{$ip}";

        // Check if IP is locked out
        if (Cache::has("locked_out_{$ip}")) {
            return response()->json(['error' => 'Too many failed attempts. Try again later.'], 429);
        }

        // Authenticate the request
        if (!$this->authenticateRequest($request)) {
            Cache::increment($cacheKey);
            $attempts = Cache::get($cacheKey, 0);

            if ($attempts >= $this->maxAttempts) {
                Cache::put("locked_out_{$ip}", true, $this->lockoutTime);
                Cache::forget($cacheKey);
                Log::warning("IP $ip has been locked out due to multiple failed callback authentication attempts.");
                AuditTrailObserver::logCustomAction("IP ${$ip} has been locked out due to multiple failed callback authentication attempts.", null, null, $ip);
                return response()->json(['error' => 'Too many failed attempts. You are locked out.'], 429);
            }

            Log::warning('AVS Callback Unauthorized Access Attempt', ['IP' => $ip, 'username' => $request->header('PHP_AUTH_USER'), 'password' => $request->header('PHP_AUTH_PW')]);
            AuditTrailObserver::logCustomAction("AVS Callback Unauthorized Access Attempt", null, null, $ip);
            /* Log::warning('AVS Callback Unauthorized Access Attempt', [
                'username' => $request->header('PHP_AUTH_USER')
            ]); */
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Reset failed attempts on successful authentication
        Cache::forget($cacheKey);
        AuditTrailObserver::logCustomAction("AVS Callback Successful authentication", null, null, $ip);

        // Process AVS Response Data
        $data = $request->all();
        $accountNumber = $data['Response']['accountNumber'] ?? null;

        if (!$accountNumber) {
            Log::warning('AVS Callback Missing Account Number', ['data' => $data]);
            return response()->json(['error' => 'Invalid request, missing account number'], 400);
        }

        // Fetch Beneficiary or Firm Account
        $account = BeneficiaryAccount::where('account_number', $accountNumber)->first()
            ?? FirmAccount::where('account_number', $accountNumber)->first();

        if ($account) {
            // Determine verification status based on AVS responses
            $avsResponses = [
                $data['Response']['accountExists'],
                $data['Response']['accountOpen'],
                $data['Response']['accountOpenGtThreeMonths'],
                $data['Response']['accountTypeValid'],
                $data['Response']['accountTypeValid'],
                '00', // Branch Code
                $data['Response']['lastNameMatch'],
                $data['Response']['initialMatch'],
                '00' // Registration Number
            ];

             // Log the AVS responses with context
            Log::info('AVS Responses:', ['avs_responses' => $avsResponses]);

            $verificationStatus = $this->isAvsVerificationSuccessful($avsResponses) ? 'successful' : 'failed';

            // Update account verification details
            $account->update([
                'verified' => true,
                'verification_status' => $verificationStatus,
                'account_found' => $data['Response']['accountExists'],
                'account_open' => $data['Response']['accountOpen'],
                'account_open_gt_three_months' => $data['Response']['accountOpenGtThreeMonths'],
                'account_type_verified' => $data['Response']['accountTypeValid'],
                'account_type_match' => $data['Response']['accountTypeValid'],
                'branch_code_match' => '00',
                'holder_name_match' => $data['Response']['lastNameMatch'],
                'holder_initials_match' => $data['Response']['initialMatch'],
                'registration_number_match' => '00',
                'avs_verified_at' => now(),
            ]);

            Log::info("AVS Callback Processed Successfully for Account: {$accountNumber}", [
                'account_id' => $account->id,
                'verification_status' => $verificationStatus
            ]);
        } else {
            Log::warning("AVS Callback Account Not Found: {$accountNumber}");
        }

        // Prepare response
        return response()->json([
            'message' => 'AVS Callback Processed Successfully',
            'account_number' => $accountNumber,
            'verification_status' => $verificationStatus ?? 'not found'
        ], 200);
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
