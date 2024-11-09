<?php

namespace App\Http\Controllers;

use App\Models\FileUpload;
use App\Models\FirmAccount;
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

    public function getFileDetails($id)
    {
        // Retrieve the specified file upload by ID, along with related requisitions and their payments
        $fileUpload = FileUpload::with([
            'requisitions' => function ($query) {
                $query->with('payments'); // Load payments for each requisition
            },
            'firmAccount.institution' // Load the firm account and institution details
        ])->find($id);

        if (!$fileUpload) {
            return response()->json(['error' => 'File not found'], 404);
        }

        // Prepare the file details
        $fileDetails = [
            'fileId' => $fileUpload->id,
            'fileReference' => $fileUpload->firmAccount->institution->short_name ." - ".$fileUpload->firmAccount->account_number . " (".\Carbon\Carbon::parse($fileUpload->generated_at)->format('Y-m-d Hi').")",
            'status' => 'Generated',
            'numberOfPayments' => 0,
            'totalAmount' => 0.00,
            'totalConfirmed' => 0.00,
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
        //dd($fileUpload->firmAccount);

        // Collect payments details
        foreach ($fileUpload->requisitions as $requisition) {
            foreach ($requisition->payments as $payment) {
                $fileDetails['payments'][] = [
                    'id' => $payment->id,
                    'fileReference' => $requisition->file_reference, // Use the file reference from the requisition
                    'recipientAccount' => $payment->beneficiaryAccount->account_number ?? 'N/A',
                    'recipientReference' => $payment->recipient_reference ?? 'N/A',
                    'myReference' => $payment->my_reference ?? 'N/A',
                    'amount' => number_format($payment->amount, 2, '.', ','),
                    'status' => $payment->status ?? 'Generated'
                ];
            }
        }

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
