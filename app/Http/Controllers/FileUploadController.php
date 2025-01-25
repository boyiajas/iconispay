<?php

namespace App\Http\Controllers;

use App\Models\FileHistoryLog;
use App\Models\FileUpload;
use App\Models\FirmAccount;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FileUploadController extends Controller
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

    public function getAllRequisitionsForAFile($id)
    {
        // Retrieve the specified file upload by ID, along with related requisitions and their details
        $fileUpload = FileUpload::with([
            'requisitions' => function ($query) {
                $query->with([
                    'payments.beneficiaryAccount.institution', // Beneficiary account with institution details
                    'payments.payToFirmAccount.institution', // Firm account with institution details
                    'payments.beneficiaryAccount.accountType', // Beneficiary account type
                    'payments.payToFirmAccount.accountType', // Firm account type
                    'payments.sourceFirmAccount', // Source firm account details
                    'deposits.firmAccount', // Deposits firm account details
                    'deposits.user', // User who created the deposit
                    'user', // Requisition user details
                    'authorizedBy', // Authorized by user details
                    'lockedBy', // Locked by user details
                    'firmAccount.institution', // Firm account institution
                ]);
            },
            'firmAccount.institution', // File firm account with institution details
            'user', // File user details
        ])->find($id);

        // Check if the file upload exists
        if (!$fileUpload) {
            return response()->json(['error' => 'File not found'], 404);
        }

        // Transform the requisitions data
        $requisitions = $fileUpload->requisitions->map(function ($requisition) {
            $requisitionData = $requisition->toArray();

            // Add file upload ID to each requisition
            $requisitionData['file_upload_id'] = $requisition->file_upload_id;

            // Transform payments to include payToAccount and beneficiary details
            $requisitionData['payments'] = $requisition->payments->map(function ($payment) {
                $paymentData = $payment->toArray();

                // Get payToAccount (Beneficiary or Firm Account)
                $payToAccount = $payment->payToFirmAccount ?? $payment->beneficiaryAccount;
                $payToAccountData = $payToAccount ? $payToAccount->toArray() : null;

                // Add institution and account type details to payToAccount
                if ($payToAccount) {
                    if ($payToAccount->institution) {
                        $payToAccountData['institution'] = $payToAccount->institution->toArray();
                    }
                    if ($payToAccount->accountType) {
                        $payToAccountData['account_type'] = $payToAccount->accountType->toArray();
                    }
                }

                // Include the transformed payToAccount in the payment data
                $paymentData['pay_to_account'] = $payToAccountData;

                return $paymentData;
            });

            return $requisitionData;
        });

        // Structure the response
        $responseData = [
            'file_upload' => $fileUpload->toArray(),
            'requisitions' => $requisitions,
        ];

        return response()->json($responseData);
    }


    public function getFileDetails($id)
    {
        // Retrieve the specified file upload by ID, along with related requisitions and their payments
        $fileUpload = FileUpload::with([
            'requisitions' => function ($query) {
                $query->with('payments'); // Load payments for each requisition
            },
            'firmAccount.institution', 'user' // Load the firm account and institution details
        ])->find($id);

        if (!$fileUpload) {
            return response()->json(['error' => 'File not found'], 404);
        }

         // Fetch the file history logs
        $fileHistory = FileHistoryLog::where('file_upload_id', $fileUpload->id)
        ->with('user') // Include the user who performed the action
        ->orderBy('log_date', 'desc') // Order by the log date
        ->get()
        ->map(function ($log) {
            return [
                'user_name' => $log->user->email ?? 'N/A',
                'action' => $log->action,
                'details' => $log->details,
                'date' => Carbon::parse($log->log_date)->format('Y-m-d H:i:s'),
            ];
        });

        // Prepare the file details
        $fileDetails = [
            'fileId' => $fileUpload->id,
            'fileReference' => $fileUpload->firmAccount->institution->short_name . " - " . $fileUpload->firmAccount->account_number . " (" . Carbon::parse($fileUpload->generated_at)->format('Y-m-d Hi') . ")",
            'status' => 'Generated',
            'numberOfPayments' => 0,
            'totalAmount' => 0.00,
            'totalConfirmed' => 0.00,
            'numberOfProcessedPayments' => 0, // Add processed payment count
            'historyLog' => $fileHistory, // Placeholder for future history log data
            'payments' => [],
            'createdBy' => $fileUpload->user->name
        ];

        // Calculate the number of payments and total amount
        $fileDetails['numberOfPayments'] = $fileUpload->requisitions->sum(function ($requisition) {
            return $requisition->payments->count();
        });

        $fileDetails['totalAmount'] = $fileUpload->requisitions->sum(function ($requisition) {
            return $requisition->payments->sum('amount');
        });

        // Collect payments details and count processed payments
        $processedPaymentsCount = 0;

        // Collect payments details
        foreach ($fileUpload->requisitions as $requisition) {
            foreach ($requisition->payments as $payment) {
                // Use the payToAccount accessor to get the correct account details
                $payToAccount = $payment->payToAccount;

                if ($payment->status === 'processed') {
                    $processedPaymentsCount++;
                    $fileDetails['totalConfirmed'] += $payment->amount;
                }//dd("output contnet", $payment->payToAccount);

                $fileDetails['payments'][] = [
                    'id' => $payment->id,
                    'requisition_id' => $requisition->id, // Add requisition_id here
                    'fileReference' => $requisition->file_reference, // Use the file reference from the requisition
                    'recipientDisplayText' => $payToAccount->display_text,
                    'recipientAccount' => $payToAccount->account_number ?? 'N/A',
                    'recipientBranchCode' => $payToAccount->branch_code ?? 'N/A',
                    'recipientReference' => $payment->recipient_reference ?? 'N/A',
                    'myReference' => $payment->my_reference ?? 'N/A',
                    'amount' => number_format($payment->amount, 2, '.', ','),
                    'status' => $payment->status ?? 'Generated',
                    'requisitionCreatedBy' => $requisition->user,
                    'payToAccountInstitution' => $payToAccount->institution->short_name ?? 'N/A' // Include institution name
                ];
            }
        }

        // Update the number of processed payments
        $fileDetails['numberOfProcessedPayments'] = $processedPaymentsCount;

        return response()->json($fileDetails);
    }


    public function getFileDetailsOld($id)
    {
        // Retrieve the specified file upload by ID, along with related requisitions and their payments
        $fileUpload = FileUpload::with([
            'requisitions' => function ($query) {
                $query->with('payments'); // Load payments for each requisition
            },
            'firmAccount.institution','user' // Load the firm account and institution details
        ])->find($id);

        if (!$fileUpload) {
            return response()->json(['error' => 'File not found'], 404);
        }

        // Prepare the file details
        $fileDetails = [
            'fileId' => $fileUpload->id,
            'fileReference' => $fileUpload->firmAccount->institution->short_name . " - " . $fileUpload->firmAccount->account_number . " (" . Carbon::parse($fileUpload->generated_at)->format('Y-m-d Hi') . ")",
            'status' => 'Generated',
            'numberOfPayments' => 0,
            'totalAmount' => 0.00,
            'totalConfirmed' => 0.00,
            'numberOfProcessedPayments' => 0, // Add processed payment count
            'historyLog' => [], // Placeholder for future history log data
            'payments' => [],
            'createdBy' => $fileUpload->user->name
        ];

        // Calculate the number of payments and total amount
        $fileDetails['numberOfPayments'] = $fileUpload->requisitions->sum(function ($requisition) {
            return $requisition->payments->count();
        });

        $fileDetails['totalAmount'] = $fileUpload->requisitions->sum(function ($requisition) {
            return $requisition->payments->sum('amount');
        });

         // Collect payments details and count processed payments
        $processedPaymentsCount = 0;
        //dd($fileUpload->firmAccount);

        // Collect payments details
        foreach ($fileUpload->requisitions as $requisition) {
            foreach ($requisition->payments as $payment) {
                if ($payment->status === 'processed') {
                    $processedPaymentsCount++;
                    $fileDetails['totalConfirmed'] += $payment->amount;
                }

                $fileDetails['payments'][] = [
                    'id' => $payment->id,
                    'requisition_id' => $requisition->id, // Add requisition_id here
                    'fileReference' => $requisition->file_reference, // Use the file reference from the requisition
                    'recipientAccount' => $payment->beneficiaryAccount->account_number ?? 'N/A',
                    'recipientReference' => $payment->recipient_reference ?? 'N/A',
                    'myReference' => $payment->my_reference ?? 'N/A',
                    'amount' => number_format($payment->amount, 2, '.', ','),
                    'status' => $payment->status ?? 'Generated'
                ];
            }
        }

         // Update the number of processed payments
        $fileDetails['numberOfProcessedPayments'] = $processedPaymentsCount;

        return response()->json($fileDetails);
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
    public function show(FileUpload $fileUpload)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FileUpload $fileUpload)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FileUpload $fileUpload)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FileUpload $fileUpload)
    {
        //
    }
}
