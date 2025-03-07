<?php

namespace App\Http\Controllers;

use App\Exports\FnbGeneratePayAwayFile;
use App\Models\AccountType;
use App\Models\Authorizer;
use App\Models\Category;
use App\Models\FileHistoryLog;
use App\Models\FileUpload;
use App\Models\FirmAccount;
use App\Models\Institution;
use App\Models\Requisition;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class FirmAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('superadmin');

        // Firm Account Query: Unrestricted for Super Admin, restricted for others
        $query = $isSuperAdmin
            ? FirmAccount::with('institution', 'category', 'organisation', 'accountType', 'authorizers.user')
            : FirmAccount::whereOrganisationId($user->organisation->id)
                ->with('institution', 'category', 'accountType', 'organisation', 'authorizers.user');

        // Fetch firm accounts and process authorizer status
        $firmaccounts = $query->get()->map(function ($firmAccount) {
            $authorizerCount = $firmAccount->authorizers->count();
            $numberOfAuthorizer = $firmAccount->number_of_authorizer ?? 0;

            if (!$firmAccount->authorised) {
                if ($authorizerCount > 0 && $authorizerCount == $numberOfAuthorizer) {
                    $firmAccount->authorised = 1;
                    $firmAccount->save();
                } else {
                    $firmAccount->authorizer_progress = "$authorizerCount of $numberOfAuthorizer";
                }
            } else {
                $firmAccount->authorizer_progress = "$authorizerCount of $numberOfAuthorizer";
            }

            return $firmAccount;
        });

        // Return formatted data
        return DataTables::of($firmaccounts)->make(true);
    }

    public function getAccounts()
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('superadmin');

        // Firm Account Query: Unrestricted for Super Admin, restricted for others
        $query = $isSuperAdmin
            ? FirmAccount::with(['institution', 'requisitions' => fn($q) => $q->whereIn('status_id', [5, 6])])
            : FirmAccount::whereOrganisationId($user->organisation->id)
                ->with(['institution', 'requisitions' => fn($q) => $q->whereIn('status_id', [5, 6])]);

        // Fetch firm accounts
        $accounts = $query->orderBy('created_at', 'desc')->get();

        // Collect all firm account IDs for batch retrieval of FileUploads
        $firmAccountIds = $accounts->pluck('id');

        // Fetch all FileUploads for the retrieved firm accounts
        $fileUploads = FileUpload::whereIn('firm_account_id', $firmAccountIds)
            ->with(['requisitions' => fn($q) => $q->where('status_id', '!=', 7)])
            ->get()
            ->groupBy('firm_account_id'); // Group by firm account for easy lookup

        // Process data
        $data = $accounts->map(function ($account) use ($fileUploads) {
            $requisitionsReadyForPayment = $account->requisitions;
            $totalRequisitions = $requisitionsReadyForPayment->count();
            $accountFileUploads = $fileUploads->get($account->id, collect());

            $totalGeneratedFiles = $accountFileUploads->filter(fn($fileUpload) =>
                $fileUpload->requisitions->where('status_id', '!=', 7)->isNotEmpty()
            )->count();

            $requisitionIdsWithFiles = $accountFileUploads->flatMap(fn($fileUpload) =>
                $fileUpload->requisitions->pluck('id')
            )->unique()->toArray();

            $newRequisitionsCount = $requisitionsReadyForPayment->whereNotIn('id', $requisitionIdsWithFiles)->count();
            $requisitionId = $requisitionsReadyForPayment->first()->id ?? null;

            return [
                'method' => ucfirst($account->method),
                'display_text' => $account->display_text,
                'institution_name' => $account->institution->name ?? 'N/A',
                'account_number' => $account->account_number,
                'ready_for_payment' => [
                    'requisition_count' => $totalRequisitions,
                    'generated_file_count' => $totalGeneratedFiles,
                    'new_requisition_count' => $newRequisitionsCount,
                    'requisition_id' => $requisitionId,
                    'account_id' => $account->id,
                ],
                'account' => [
                    'pending_confirmation_files' => $account->pending_confirmation_files,
                    'account_id' => $account->id,
                    'account_number' => $account->account_number,
                ]
            ];
        });

        return response()->json(['data' => $data]);
    }



    public function filesDetails(Request $request, $sourceAccountId)
    {
        // Retrieve the FirmAccount along with related files, institution, and payments associated with each requisition
        $firmAccount = FirmAccount::with([
            'files.requisitions.payments.beneficiaryAccount.institution',
            'files.user',
            'institution',
        ])->find($sourceAccountId);

        // Check if the FirmAccount exists
        if (!$firmAccount) {
            return response()->json(['error' => 'Firm account not found.'], 404);
        }

        // Prepare the file details to return
        $fileDetails = [
            'accountName' => $firmAccount->display_text,
            'institution' => $firmAccount->institution->name ?? 'N/A',
            'accountNumber' => $firmAccount->account_number,
            'accountHolder' => $firmAccount->account_holder,
            'timeGenerated' => $firmAccount->created_at ? $firmAccount->created_at->format('d M Y H:i') : 'N/A',
            'createdBy' => $firmAccount->created_by ?? 'N/A',
            'statusMessage' => 'The Payaway file is ready to download.',
            'files' => $firmAccount->files->map(function ($file) {
                return [
                    'id' => $file->id,
                    'fileReference' => $file->file_reference ?? 'N/A',
                    'generatedBy' => $file->user->name ?? 'N/A', // Get the user name
                    'requisitions' => $file->requisitions->where('status_id', 5)->map(function ($requisition) {
                        // Fetch the full Requisition object using the requisition ID
                        $fullRequisition = Requisition::find($requisition->id);

                        return [
                            'id' => $requisition->id,
                            // Access the file_reference from the full Requisition object
                            'fileReference' => $fullRequisition ? $fullRequisition->file_reference : 'N/A',
                            'payments' => $requisition->payments->map(function ($payment) {
                                return [
                                    'id' => $payment->id,
                                    'recipientAccount' => $payment->beneficiaryAccount->account_number ?? 'N/A',
                                    'recipientInstitution' => $payment->beneficiaryAccount->institution->name ?? 'N/A',
                                    'recipientReference' => $payment->recipient_reference ?? 'N/A',
                                    'myReference' => $payment->my_reference ?? 'N/A',
                                    'amount' => number_format($payment->amount, 2, '.', ','),
                                ];
                            }),
                        ];
                    }),
                ];
            }),
            'totalAmount' => number_format($firmAccount->files->flatMap(function ($file) {
                return $file->requisitions->where('status_id', 5)->flatMap(function ($requisition) {
                    return $requisition->payments->pluck('amount');
                });
            })->sum(), 2, '.', ','),
            'numberOfEntries' => $firmAccount->files->count(),
        ];
        //dd($fileDetails);

        return response()->json($fileDetails);
    }

    public function authorise(Request $request, $sourceAccountId)
    {
        // Find the FirmAccount by ID
        $firmAccount = FirmAccount::findOrFail($sourceAccountId);

        // Check if the firm account has already been authorized
        if ($firmAccount->authorised) {
            return response()->json([
                'message' => 'This firm account has already been authorised.'
            ], 201);
        }

        // Check if the user has already authorized this firm account
        $userId = $request->user()->id; // Assuming the user is authenticated and you have their ID
        $existingAuthorizer = Authorizer::where('firm_account_id', $firmAccount->id)
            ->where('user_id', $userId)
            ->first();

        if ($existingAuthorizer) {
            return response()->json([
                'message' => 'You have already authorized this firm account.'
            ], 201);
        }

        // Create a new Authorizer record
        Authorizer::create([
            'firm_account_id' => $firmAccount->id,
            'beneficiary_account_id' => null, // You can set this if applicable
            'user_id' => $userId,
            'organisation_id' => Auth::user()->organisation->id
        ]);

        // Count the number of authorizer entries for this firm account
        $authorizerCount = $firmAccount->authorizers()->count();
        $numberOfAuthorizer = $firmAccount->number_of_authorizer;

        // Check if the current number of authorizers meets the required number
        if ($authorizerCount >= $numberOfAuthorizer) {
            // Mark the firm account as authorised
            $firmAccount->authorised = 1;
            $firmAccount->save();

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

    public function generateFile(Request $request, $sourceAccountId)
    {
        $firmAccount = FirmAccount::with('institution')->findOrFail($sourceAccountId);

        // Retrieve all requisitions for the specified source account with status ready for payment
        $requisitions = Requisition::where('firm_account_id', $sourceAccountId)
            ->where('status_id', 5)
            ->whereDoesntHave('fileUploads') // Exclude requisitions that are already attached to a file upload
            ->with('fileUploads') // Eager load file uploads to avoid N+1 problem
            ->get();

        if ($requisitions->isEmpty()) {
            return response()->json(['message' => 'No new requisitions available for payment.'], 400);
        }

        // Generate a consolidated file for all payments
        $fileName = "Default-{$firmAccount->account_number} " . now()->format('YmdHis') . '.txt';
        $filePath = storage_path("app/files/{$fileName}");

        // Ensure the directory exists
        $directory = storage_path('app/files');
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Create the file and add information about each requisition
        $fileContent = '';
        $requisitionIds = []; // Collect requisition IDs to save in FileUpload
        $bank = $firmAccount->institution->short_name;
        $absacount = 1001;
        $company_name = $firmAccount->organisation->name;
        $increment = 1;
        $standardBankLastRowFileContent = '';
        $standardBankFirstRowFileContent = '';
        $nedBankLastRowFileContent = '';
        $nedBankFirstRowFileContent = '';
        $excelData = [
            'date' => now()->format('d/m/Y'), // Set dynamic date for the second row
            'firmAccountNumber' => $firmAccount->account_number, // Firm account number for the third row
            'tableData' => [], // Initialize table data
        ];

        foreach ($requisitions as $requisition) {
            
            foreach ($requisition->payments as $payment) {
                // Retrieve required data
                $payToAccount = $payment->payToAccount;
                $accountNumber = $payToAccount ? $payToAccount->account_number : '';
                $branchCode = $payToAccount ? $payToAccount->branch_code : '';
                $recipientReference = $payment->recipient_reference ?? '';
                $amount = number_format($payment->amount, 2, '.', '');
                $recipientReference2 = $recipientReference;
                $TodisplayName = $payToAccount ? $payToAccount->display_text : '';

                $totalAmount =  $requisition->payments->sum('amount');
                $totalAmountDecimal = number_format($totalAmount, 2, '.', '');
                // Remove the decimal point by replacing '.' with ''
                $totalAmountFinal = str_replace('.', '', $totalAmountDecimal);
                 // Build the content of the file
                switch ($bank) {
                    case 'ABSA':
                    case 'Capitec':
                    case 'Capitec Business':

                        // Specify the words with their desired start positions
                        $wordsWithIndices = [
                            0 => "ABSADATA",
                            12 => strtoupper("3450000" . $absacount . "C" . $company_name),
                            60 => $firmAccount->account_number,
                            70 => $firmAccount->branch_code . "3",
                            78 => strtoupper($payment->my_reference),
                            108 => strtoupper($TodisplayName),
                            140 => $accountNumber,
                            154 => $branchCode . "3",
                            162 => strtoupper($recipientReference),
                            199 => $amount,
                            210 => Carbon::parse($payment->created_at)->format('ymd') . "N  0000000CNAD HOC\t I",
                        ];
                        // Format the sentence
                        $fileContent .= $this->formatSentenceFixedColumns($wordsWithIndices) . "\n";
                        $absacount += 1000; // Increment counter for unique identifiers

                        break;
                    
                    case 'Standard':

                        // Define the lines with their index positions
                        if($standardBankFirstRowFileContent === ''){
                            // SB line
                            $sbLine = $this->formatSentenceFixedColumns([
                                0 => "SB" . Carbon::parse($payment->created_at)->format('Ymd') . $firmAccount->account_number . " (" . Carbon::parse($payment->created_at)->format('Ymd Hi') . ")",
                            ]);
                            $standardBankFirstRowFileContent .= $sbLine . "\n";
                        }

                        // SD line
                        $sdLine = $this->formatSentenceFixedColumns([
                            0 => "SD".$this->padNumber(3, $increment++) . "0000000000C" .  $this->padNumber(6, $branchCode) . $this->padNumber(13, $accountNumber) . $TodisplayName,
                            81 => $this->padNumber(15, str_replace('.', '', $amount)) . $recipientReference,
                        ]);
                        $fileContent .= $sdLine . "\n";

                        if($standardBankLastRowFileContent === ''){
                            // SC line
                            $scLine = $this->formatSentenceFixedColumns([
                                0 => "SC001",
                                35 => "D051001" . $this->padNumber(13, $firmAccount->account_number) . "PX " . Carbon::parse($payment->created_at)->format('Ymd'),
                                85 => strtoupper($company_name),
                            ]);
                            $standardBankLastRowFileContent .= $scLine . "\n";

                            // ST line
                            $stLine = $this->formatSentenceFixedColumns([
                                0 => "ST0000001" . $this->padNumber(7, $requisition->payments->count()) . "00100" . $this->padNumber(15, $totalAmountFinal) . $this->padNumber(15, $totalAmountFinal),
                            ]);
                            $standardBankLastRowFileContent .= $stLine . "\n";
                        }

                        break;

                    case 'Nedbank':

                        // Define the lines with their index positions
                        if($nedBankFirstRowFileContent === ''){
                            // SB line
                            $sbLine = $this->formatSentenceFixedColumns([
                                0 => "0210010000000000000021" . Carbon::parse($payment->created_at)->format('ymd') . Carbon::parse($payment->created_at)->format('ymd') . "000118000180MAGTAPE",
                            ]);
                            $nedBankFirstRowFileContent .= $sbLine . "\n";
                            // SB line
                            $sbLine = $this->formatSentenceFixedColumns([
                                0 => "040000" . 
                                    Carbon::parse($payment->created_at)->format('ymd') . 
                                    Carbon::parse($payment->created_at)->format('ymd') . 
                                    Carbon::parse($payment->created_at)->format('ymd'). 
                                    Carbon::parse($payment->created_at)->format('ymd') ."0000010001ONE DAY",
                            ]);
                            $nedBankFirstRowFileContent .= $sbLine . "\n";
                        }

                        // SD line
                        $sdLine = $this->formatSentenceFixedColumns([
                            0 => "10".$firmAccount->branch_code . 
                                $this->padNumber(11, $firmAccount->account_number) .
                                $this->padNumber(10, $increment++) .  
                                $this->padNumber(6, $branchCode) . 
                                $this->padNumber(11, $accountNumber) . "1" . 
                                $this->padNumber(11, number_format($amount, 2, '', '')) .  
                                Carbon::parse($payment->created_at)->format('ymd') . "000000 " . $recipientReference, 
                            100 => $TodisplayName,
                            130 => "00000000000000000000",
                            166 => "21",
                        ]);

                        $fileContent .= $sdLine . "\n";

                        if($nedBankLastRowFileContent === '' && $increment > $requisition->payments->count()){
                            // SC line
                            $scLine = $this->formatSentenceFixedColumns([
                                0 => "12".$firmAccount->branch_code . 
                                    $this->padNumber(11, $firmAccount->account_number) .
                                    $this->padNumber(10, $increment) .  
                                    $this->padNumber(6, $branchCode) . 
                                    $this->padNumber(11, $accountNumber) . "1" . 
                                    $this->padNumber(11, $totalAmountFinal) . Carbon::parse($payment->created_at)->format('ymd') . "100000" . $firmAccount->account_holder, 
                                100 => $company_name,
                            ]);
                            $nedBankLastRowFileContent .= $scLine . "\n";

                            // ST line
                            $stLine = $this->formatSentenceFixedColumns([
                                0 => "920000000001" . 
                                    $this->padNumber(6, $increment) . 
                                    Carbon::parse($payment->created_at)->format('ymd') .
                                    Carbon::parse($payment->created_at)->format('ymd') . 
                                    $this->padNumber(7, $requisition->payments->count()) . "000001000001" .
                                    $this->padNumber(12, $totalAmountFinal).
                                    $this->padNumber(12, $totalAmountFinal).
                                    "069225840312"
                            ]);
                            $nedBankLastRowFileContent .= $stLine . "\n";

                            // ST line
                            $stLine = $this->formatSentenceFixedColumns([
                                0 => "9410010000000000000021" . Carbon::parse($payment->created_at)->format('ymd') . Carbon::parse($payment->created_at)->format('ymd') . "000118000180MAGTAPE",
                                56 => "000001000008000002",
                            ]);
                            $nedBankLastRowFileContent .= $stLine . "\n";
                        }

                        break;

                    case 'FNB':

                        $excelData['tableData'][] = [
                            'RECIPIENT NAME' => $payToAccount->display_text ?? 'N/A',
                            'RECIPIENT ACCOUNT' => $payToAccount->account_number ?? 'N/A',
                            'RECIPIENT ACCOUNT TYPE' => ($payToAccount->account_type_id - 1) ?? 'N/A',
                            'BRANCHCODE' => $payToAccount->branch_code ?? 'N/A',
                            'AMOUNT' => number_format($payment->amount, 2, '.', ''),
                            'OWN REFERENCE' => $payment->my_reference ?? 'N/A',
                            'RECIPIENT REFERENCE' => $payment->recipient_reference ?? 'N/A',
                            'EMAIL 1 NOTIFY' => '',
                            'EMAIL 1 ADDRESS' => '',
                            'EMAIL 1 SUBJECT' => '',
                            'EMAIL 2 NOTIFY' => '',
                            'EMAIL 2 ADDRESS' => '',
                            'EMAIL 2 SUBJECT' => '',
                            'EMAIL 3 NOTIFY' => '',
                            'EMAIL 3 ADDRESS' => '',
                            'EMAIL 3 SUBJECT' => '',
                            'EMAIL 4 NOTIFY' => '',
                            'EMAIL 4 ADDRESS' => '',
                            'EMAIL 4 SUBJECT' => '',
                            'EMAIL 5 NOTIFY' => '',
                            'EMAIL 5 ADDRESS' => '',
                            'EMAIL 5 SUBJECT' => '',
                            'FAX 1 NOTIFY' => '',
                            'FAX 1 CODE' => '',
                            'FAX 1 NUMBER' => '',
                            'FAX 1 SUBJECT' => '',
                            'FAX 2 NOTIFY' => '',
                            'FAX 2 CODE' => '',
                            'FAX 2 NUMBER' => '',
                            'FAX 2 SUBJECT' => '',
                            'SMS 1 NOTIFY' => '',
                            'SMS 1 CODE' => '',
                            'SMS 1 NUMBER' => '',
                            'SMS 2 NOTIFY' => '',
                            'SMS 2 CODE' => '',
                            'SMS 2 NUMBER' => '',
                        ];
                        
                        break;
    
                    default:
                        // Handle other banks if necessary
                        break;
                }


            }
        
            $requisitionIds[] = $requisition->id; // Track requisition IDs

            // Update requisition status to processed (6)
            $requisition->update(['status_id' => 6]);
            
        }

        if($bank === 'FNB'){
            /* if (empty($excelData['tableData'])) {
                return response()->json(['message' => 'No payments found to export.'], 400);
            } */

            // Clean output buffer to avoid corruption
            if (ob_get_contents()) {
                ob_end_clean();
            }

            // File name for the Excel export
            //$fileName = "FNB_Payment_File_{$firmAccount->account_number}_" . now()->format('Ymd_His') . '.xlsx';
            // Generate the CSV file
            $fileName = "FNB_Payment_File_{$firmAccount->account_number}_" . now()->format('Ymd_His') . '.csv';

    

            $filePath = storage_path("app/files/{$fileName}");
            // Use Maatwebsite to export as CSV
            $fileContent = Excel::store(new FnbGeneratePayAwayFile($excelData), "files/{$fileName}");

            //$fileContent = Excel::store(new FnbGeneratePayAwayFile($excelData), "files/{$fileName}");

            //dd($fileContent);
        }

        if($bank === 'Standard'){ //here we want to add the last part of the rows to the standard bank file
            $fileContent = $standardBankFirstRowFileContent . $fileContent;
            $fileContent .= $standardBankLastRowFileContent;
        }       

        if($bank === 'Nedbank'){ //here we want to add the last part of the rows to the standard bank file
            $fileContent = $nedBankFirstRowFileContent . $fileContent;
            $fileContent .= $nedBankLastRowFileContent;
        }       

        if($bank === 'FNB'){

        }else{
             // Write content to the file
            file_put_contents($filePath, $fileContent);
        }

        $fileHash = hash_file('sha256', $filePath);  // Generate the hash for the file
        

        // Save a single file record in FileUpload with the requisition_ids as JSON
        $fileUpload = FileUpload::create([
            'firm_account_id' => $sourceAccountId, // Associate the file with the firm account
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_size' => filesize($filePath) / 1024, // File size in KB
            'file_hash' => $fileHash,
            'user_id' => auth()->user()->id,
            'generated_at' => now(),
            'organisation_id' => Auth::user()->organisation->id,
        ]);

        // Log actions
        FileHistoryLog::logFileHistory($fileUpload->id, 'Created Payaway File', "Created payaway file {$firmAccount->institution->short_name} - {$firmAccount->account_number} ({$fileUpload->created_at->format('Ymd Hi')})");
        FileHistoryLog::logFileHistory($fileUpload->id, 'Created Hash', "Added a hash validation to the file");


        // Attach all requisitions to the file upload in a single query
        $fileUpload->requisitions()->attach($requisitionIds);

        // Retrieve the specified file upload by ID, along with related requisitions and their payments
        $fileUpload = FileUpload::with([
            'requisitions' => function ($query) {
                $query->with('payments', 'payments.payToFirmAccount.institution', 'payments.beneficiaryAccount.institution');
            },
            'firmAccount.institution', // Load the firm account and institution details
            'user'
        ])->find($fileUpload->id);

        if (!$fileUpload) {
            return response()->json(['error' => 'File not found'], 404);
        }

        // Prepare the file details
        $fileDetails = [
            'fileId' => $fileUpload->id,
            'accountName' => $fileUpload->firmAccount->display_text,
            'accountHolder' => $fileUpload->firmAccount->account_holder,
            'accountNumber' => $fileUpload->firmAccount->account_number,
            'status' => 'Generated',
            'numberOfPayments' => 0,
            'totalAmount' => 0.00,
            'totalConfirmed' => 0.00,
            'timeGenerated' => Carbon::parse($fileUpload->created_at)->format('d M Y Hi'),
            'createdBy' => $fileUpload->user->name,
            'institution' => $fileUpload->firmAccount->institution->name,
            'statusMessage' => 'The Payaway file is ready to download.',
            'historyLog' => [], // Placeholder for future history log data
            'payments' => []
        ];

        // Calculate the number of payments and total amount
        $fileDetails['numberOfPayments'] = $fileUpload->requisitions->sum(function ($requisition) {
            return $requisition->payments->count();
        });

        $fileDetails['totalAmount'] = $fileUpload->requisitions->sum(function ($requisition) {
            return $requisition->payments->sum('amount');
        });

        // Collect payments details
        foreach ($fileUpload->requisitions as $requisition) {
            foreach ($requisition->payments as $payment) {
                $payToAccount = $payment->payToAccount;
                $institution = $payToAccount && $payToAccount->institution ? $payToAccount->institution->name : 'N/A';

                $fileDetails['payments'][] = [
                    'fileReference' => $requisition->file_reference,
                    'recipientAccount' => $payToAccount ? $payToAccount->account_number : 'N/A',
                    'recipientReference' => $payment->recipient_reference ?? 'N/A',
                    'myReference' => $payment->my_reference ?? 'N/A',
                    'amount' => number_format($payment->amount, 2, '.', ','),
                    'status' => $payment->status ?? 'Generated',
                    'beneficiaryAccount' => $payToAccount,
                    'institution' => $institution
                ];
            }
        }

        return response()->json([
            'message' => 'File generated successfully for all new requisitions.',
            'file' => $fileDetails,
        ]);
    }

    private function formatSentenceFixedColumns(array $wordsWithIndices)
    {
        // Initialize an empty string with enough spaces to accommodate the maximum index
        $maxIndex = max(array_keys($wordsWithIndices));
        $sentence = str_repeat(" ", $maxIndex + 50); // Buffer for safety
    
        foreach ($wordsWithIndices as $targetIndex => $word) {
            // Ensure $targetIndex is an integer
            $targetIndex = (int) $targetIndex;
            //$targetIndex = ($targetIndex == 0) ? 0 : $targetIndex + 1;
    
            // Insert the word at the exact target index
            $sentence = substr_replace($sentence, $word, $targetIndex, strlen($word));
        }
    
        return $sentence;
    }

    // Private function to pad a number with leading zeros
    private function padNumber($length, $value)
    {
        return str_pad($value, $length, '0', STR_PAD_LEFT);
    }
    

    public function getIndividualAccountPendingConfirmationFiles($id)
    {
        // Retrieve pending confirmation files for the specified FirmAccount ID
        $pendingFiles = FirmAccount::where('id', $id)
            ->whereHas('requisitions', function ($query) {
                $query->where('status_id', 6); // Only requisitions with 'pending_confirmation' status
            })
            ->with(['requisitions' => function ($query) {
                $query->where('status_id', 6)
                    ->has('fileUploads')// Ensure requisitions have file uploads
                    ->with('fileUploads', 'payments'); // Load related file uploads and payments
            }, 'institution'])
            ->get();

        // Group requisitions by generated file
        $data = $pendingFiles->flatMap(function ($account) {
            // Create a map of generated files to requisitions
            $groupedRequisitions = $account->requisitions->groupBy(function ($requisition) {
                return $requisition->fileUploads->first()->generated_at; // Group by the generated_at date of the first file upload
            });

            return $groupedRequisitions->map(function ($requisitions, $generatedAt) use ($account) {
                $totalPayments = $requisitions->sum(function ($requisition) {
                    return $requisition->payments->count();
                });

                $totalAmount = $requisitions->sum(function ($requisition) {
                    return $requisition->calculateTransactionValue();
                });

                $dateGenerated = Carbon::parse($generatedAt)->format('d M Y H:i');

                $fileId = $requisitions->first()->fileUploads->first()->id; // Get the file ID from the first file upload

                // HTML for the edit button
                $editButton = "<span class='pull-right btn btn-sm btn-default-default py-0 px-1 file-management-btn' data-file-id='{$fileId}'><i class='fas fa-edit'></i></span>";

                return [
                    'display' => $account->display_text,
                    'file_name' => "Default - {$account->account_number} (".Carbon::parse($generatedAt)->format('Y-m-d Hi').")",
                    'payments' => $totalPayments,
                    'date_generated' => $dateGenerated,
                    'total_amount' => $totalAmount,
                    'status' => '<span class="badge bg-info" style="width: 95px;border-radius: 3px;height: 20px;">Generated</span> ' . $editButton,
                ];
            });
        })->values()->all(); // Convert entire result to array format for JSON response

        return response()->json(['data' => $data]);
    }

    public function getIndividualAccountRecentlyClosedFiles($id)
    {
        // Retrieve pending confirmation files for the specified FirmAccount ID
        $pendingFiles = FirmAccount::where('id', $id)
            ->whereHas('requisitions', function ($query) {
                $query->where('status_id', 7); // Only requisitions with 'pending_confirmation' status
            })
            ->with(['requisitions' => function ($query) {
                $query->where('status_id', 7)
                    ->with('fileUploads', 'payments'); // Load related file uploads and payments
            }, 'institution'])
            ->get();

        // Flatten and map each account's requisitions and file uploads
        $data = $pendingFiles->flatMap(function ($account) {
            return $account->requisitions->map(function ($requisition) use ($account) {
                // Check if any file uploads exist for this requisition
                $hasFiles = $requisition->fileUploads->isNotEmpty();
                $dateGenerated = $hasFiles ? $requisition->fileUploads->max('generated_at')->format('d M Y H:i') : '<span class="badge bg-default">None</span>';
                // Ensure that `fileId` is properly handled
                $fileId = $requisition->fileUploads->first()->id ?? null;
                 // HTML for the edit button
                 $editButton = "<span class='pull-right btn btn-sm btn-default-default py-0 px-1 file-management-btn' data-file-id='{$fileId}' ><i class='fas fa-edit'></i></span>";

                return [
                    'display_text' => $account->display_text,
                    'default_file_name' => "Default - {$account->account_number}",
                    'payments' => $requisition->payments->count(),
                    'date_generated' => $dateGenerated,
                    'date_completed' => ($requisition && $requisition->completed_at) ? Carbon::parse($requisition->completed_at)->format('d M Y H:i') : '',
                    'total_amount' => $requisition->calculateTransactionValue(),
                    'status' => ($hasFiles ? '<span class="badge bg-success" style="width: 95px;border-radius: 3px;height: 20px;">Processed</span>' : '<span class="badge bg-default">No open files</span>') . ' ' . $editButton,
                
                    'files' => $requisition->fileUploads->map(function ($fileUpload) {
                        return [
                            'file_name' => $fileUpload->file_name, // Name of the generated file
                            'file_id' => $fileUpload->id,
                            'download_url' => route('secure.download', ['fileId' => $fileUpload->id]), // Secure download route
                            'date_generated' => $fileUpload->generated_at->format('d M Y H:i'), // File generation date
                        ];
                    })->all()
                
                ];
            });
        })->all(); // Convert entire result to array format for JSON response

        return response()->json(['data' => $data]);
    }

    public function getPendingConfirmationFiles()
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('superadmin');

        // Firm Account Query: Unrestricted for Super Admin, restricted for others
        $query = $isSuperAdmin
            ? FirmAccount::with(['institution', 'requisitions' => function ($q) {
                $q->where('status_id', 6)
                ->has('fileUploads')
                ->with('fileUploads', 'payments');
            }])
            : FirmAccount::whereOrganisationId($user->organisation->id)
                ->with(['institution', 'requisitions' => function ($q) {
                    $q->where('status_id', 6)
                    ->has('fileUploads')
                    ->with('fileUploads', 'payments');
                }]);

        $pendingFiles = $query->whereHas('requisitions.fileUploads', function ($q) {
            $q->where('status_id', 6);
        })->get();

        // Group requisitions by generated file
        $data = $pendingFiles->flatMap(function ($account) {
            return $account->requisitions->groupBy(fn($requisition) =>
                $requisition->fileUploads->first()->generated_at
            )->map(function ($requisitions, $generatedAt) use ($account) {
                $totalPayments = $requisitions->sum(fn($requisition) => $requisition->payments->count());
                $totalAmount = $requisitions->sum(fn($requisition) => $requisition->calculateTransactionValue());
                $dateGenerated = Carbon::parse($generatedAt)->format('d M Y H:i');
                $fileId = $requisitions->first()->fileUploads->first()->id;

                $editButton = "<span class='pull-right btn btn-sm btn-default-default py-0 px-1 file-management-btn' data-file-id='{$fileId}'><i class='fas fa-edit'></i></span>";

                return [
                    'display' => $account->display_text,
                    'default_file_name' => "Default - {$account->account_number} (" . Carbon::parse($generatedAt)->format('Y-m-d Hi') . ")",
                    'payments' => $totalPayments,
                    'date_generated' => $dateGenerated,
                    'total_amount' => $totalAmount,
                    'status' => '<span class="badge bg-info" style="width: 95px;border-radius: 3px;height: 20px;">Generated</span> ' . $editButton,
                ];
            });
        })->values()->all();

        return response()->json(['data' => $data]);
    }


    /**
     * Get Recently Closed Files for the Recently Closed Files Table.
     */

     public function getRecentlyClosedFiles(Request $request)
     {
         $user = Auth::user();
         $isSuperAdmin = $user->hasRole('superadmin');
     
         $fromDate = $request->from_date ? Carbon::parse($request->from_date)->startOfDay() : Carbon::today()->startOfDay();
         $toDate = $request->to_date ? Carbon::parse($request->to_date)->endOfDay() : Carbon::today()->endOfDay();
     
         // Firm Account Query: Unrestricted for Super Admin, restricted for others
         $query = $isSuperAdmin
             ? FirmAccount::with(['institution', 'requisitions' => function ($q) use ($fromDate, $toDate) {
                 $q->where('status_id', 7)->whereBetween('updated_at', [$fromDate, $toDate])
                   ->with('fileUploads', 'payments');
             }])
             : FirmAccount::whereOrganisationId($user->organisation->id)
                 ->with(['institution', 'requisitions' => function ($q) use ($fromDate, $toDate) {
                     $q->where('status_id', 7)->whereBetween('updated_at', [$fromDate, $toDate])
                       ->with('fileUploads', 'payments');
                 }]);
     
         $closedFiles = $query->whereHas('requisitions', function ($q) {
             $q->where('status_id', 7);
         })->get();
     
         // Process each account's requisitions
         $data = $closedFiles->flatMap(function ($account) {
             return $account->requisitions->map(function ($requisition) use ($account) {
                 $hasFiles = $requisition->fileUploads->isNotEmpty();
                 $dateGenerated = $hasFiles ? $requisition->fileUploads->max('generated_at')->format('d M Y H:i') : '<span class="badge bg-default">None</span>';
                 $fileId = $requisition->fileUploads->first()->id ?? null;
     
                 $editButton = "<span class='pull-right btn btn-sm btn-default-default py-0 px-1 file-management-btn' data-file-id='{$fileId}' ><i class='fas fa-edit'></i></span>";
     
                 return [
                     'display_text' => $account->display_text,
                     'default_file_name' => "Default - {$account->account_number}",
                     'payments' => $requisition->payments->count(),
                     'date_generated' => $dateGenerated,
                     'date_completed' => $requisition->completed_at ? Carbon::parse($requisition->completed_at)->format('d M Y H:i') : '',
                     'total_amount' => $requisition->calculateTransactionValue(),
                     'status' => ($hasFiles
                         ? '<span class="badge bg-success" style="width: 95px;border-radius: 3px;height: 20px;">Processed</span>'
                         : '<span class="badge bg-default">No open files</span>') . ' ' . $editButton,
                     'files' => $requisition->fileUploads->map(fn($fileUpload) => [
                         'file_name' => $fileUpload->file_name,
                         'file_id' => $fileUpload->id,
                         'download_url' => route('secure.download', ['fileId' => $fileUpload->id]),
                         'date_generated' => $fileUpload->generated_at->format('d M Y H:i'),
                     ])->all()
                 ];
             });
         })->all();
     
         return response()->json(['data' => $data]);
     }
     


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function importFirmAccounts(Request $request)
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
                'accountNumber' => 'required|string|max:50|unique:firm_accounts,account_number',
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
            //dd($row, $accountCategory, $accountType, $institution);
            // Retrieve category, account type, and institution IDs using LIKE for fuzzy search
            /* $categoryId = Category::where('LOWER(name)', 'LIKE', '%' . strtolower($accountCategory) . '%')->value('id');
            $accountTypeId = AccountType::where('LOWER(name)', 'LIKE', '%' . strtolower($accountType) . '%')->value('id');
            $institutionId = Institution::where('LOWER(name)', 'LIKE', '%' . strtolower($institution) . '%')->value('id');
 */
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
            if (FirmAccount::where('account_number', $accountNumber)->exists()) {
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
            $firmAccount = FirmAccount::create([
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
                'organisation_id' => Auth::user()->organisation->id,
            ]);

            $importedAccounts[] = $firmAccount;
        }

        return response()->json([
            'message' => 'Firm accounts imported successfully!',
            'imported_accounts' => $importedAccounts,
            'errors' => $errors,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         // Validate the incoming request
         $validated = $request->validate([
            'displayText' => 'required|string|max:255',
            'accountHolderType' => 'required|in:natural,juristic',
            'accountNumber' => 'required|string|max:50',
            'accountCategory' => 'required|integer',
            'accountType' => 'required|exists:account_types,id',
            'institution' => 'required|exists:institutions,id',
            'branchCode' => 'nullable|string|max:20',
            'initials' => 'nullable|string|max:10',
            'surname' => 'nullable|string|max:100',
            'companyName' => 'nullable|string|max:255',
            'idNumber' => 'nullable|string|max:50',
            'registrationNumber' => 'nullable|string|max:100',
            'myReference' => 'nullable|string|max:100',
            'recipientReference' => 'nullable|string|max:100',
            'verified' => 'boolean',
            'number_of_authorizer' => 'nullable|integer',
            'emailAddress' => 'nullable|string|email|max:255',
            'phoneNumber' => 'nullable|string|max:20',
            //'numberOfAuthorizer' => 'required|array', // List of authorizers
        ]);

        // Check if the accountNumber already exists
        $existingAccount = FirmAccount::where('account_number', $request->input('accountNumber'))->first();
        if ($existingAccount) {
            return response()->json([
                'message' => 'The account number already exists.'
            ], 400);
        }

         // Create the FirmAccount
        $firmAccount = FirmAccount::create([
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
            'organisation_id' => $request->organisation_id ? $request->organisation_id : Auth::user()->organisation->id,
        ]);


       
        //$firmAccount = FirmAccount::create($validated);
         
        return response()->json($firmAccount, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(FirmAccount $firmAccount)
    {
        // Return the firmAccount with any required relationships, such as the user who created it
        return response()->json($firmAccount->load('institution','deposits.user','accountType','payments.user','payments.accountType', 'payments.institution'));
    }

    public function getAllFirmAccounts()
    {
        try {
            $user = Auth::user();
            $isSuperAdmin = $user->hasRole('superadmin');

            // Firm Account Query: Unrestricted for Super Admin, restricted for others
            $query = $isSuperAdmin
                ? FirmAccount::select('id', 'account_number', 'display_text')
                : FirmAccount::whereOrganisationId($user->organisation->id)
                    ->select('id', 'account_number', 'display_text');

            // Fetch the accounts and map to a custom structure
            $accounts = $query->get()->map(function ($account) {
                return [
                    'id' => $account->id,
                    'name' => $account->display_text . ' (' . $account->account_number . ')',
                ];
            });

            // Return the accounts as JSON
            return response()->json([
                'success' => true,
                'accounts' => $accounts,
            ], 200);

        } catch (\Exception $e) {
            // Handle any unexpected errors
            return response()->json([
                'success' => false,
                'message' => 'Error fetching firm accounts: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FirmAccount $firmAccount)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FirmAccount $firmAccount)
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
        $existingAccount = FirmAccount::where('account_number', $request->input('account_number'))
                                    ->where('id', '!=', $firmAccount->id)
                                    ->first();
        if ($existingAccount) {
            return response()->json([
                'message' => 'The account number already exists.'
            ], 400);
        }

        // Update the FirmAccount
        $firmAccount->update([
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
            'message' => 'Firm Account updated successfully!',
            'firmAccount' => $firmAccount
        ], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FirmAccount $firmAccount)
    {
         // Get all associated requisitions
         $requisitions = $firmAccount->requisitions;
        if(count($requisitions) > 0){
            // Create an instance of the RequisitionController
            $requisitionController = new RequisitionController();

            // Loop through each Requisition and call the destroy method
            foreach ($requisitions as $requisition) {
                $requisitionController->destroy($requisition);
            }
        }

        $firmAccount->delete();
        return response()->json(['message' => 'Firm Account deleted successfully.']);
    }
}
