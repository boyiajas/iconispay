<?php

namespace App\Http\Controllers;

use App\Models\FirmAccount;
use Illuminate\Http\Request;
use DataTables;

class FirmAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get the FirmAccount data with related 'institution'
        $firmaccounts = FirmAccount::with('institution','category','accounttype');

        // Use the DataTables facade to return data in the required format
        return DataTables::of($firmaccounts)->make(true);
    }

    // Get Accounts for Accounts Table
    /* public function getAccounts()
    {
        $accounts = FirmAccount::with('institution')->get();
        
        // Customize the output to match the DataTable columns
        $data = $accounts->map(function ($account) {
            return [
                'method' => ucfirst($account->method), // Manual or File Upload
                'display' => $account->display,
                'institution_name' => $account->institution->name,
                'account_number' => $account->account_number,
                'ready_for_payment' => $account->ready_for_payment,
                'pending_confirmation_files' => $account->pending_confirmation_files > 0 ? "Default - {$account->account_number}" : "No open files",
                /* 'ready_for_payment' => "{$account->ready_for_payment} Matter(s)",
                'pending_confirmation_files' => $account->pending_confirmation_files_count > 0 ? "Default - {$account->account_number}" : "No open files", *
            ];
        });

        return response()->json(['data' => $data]);
    }
 */

    public function getAccounts()
    {
        $accounts = FirmAccount::with('institution')->get();

        // Customize the output to include the number of requisitions ready for payment and generated files
        $data = $accounts->map(function ($account) {
            // Get the count of requisitions ready for payment
            $requisitionsReadyForPayment = $account->requisitions()
                ->where('status_id', 5)
                ->withCount('fileUploads') // Get the number of generated files per requisition
                ->get();

            $totalRequisitions = $requisitionsReadyForPayment->count();
            $totalGeneratedFiles = $requisitionsReadyForPayment->sum('file_uploads_count'); // Sum up all generated files

            // Assuming we want to use the first requisition's ID for this example
            $requisitionId = $requisitionsReadyForPayment->first()->id ?? null;

            return [
                'method' => ucfirst($account->method),
                'display' => $account->display,
                'institution_name' => $account->institution->name ?? 'N/A',
                'account_number' => $account->account_number,
                'ready_for_payment' => [
                    'requisition_count' => $totalRequisitions,
                    'generated_file_count' => $totalGeneratedFiles,
                    'requisition_id' => $requisitionId,
                ],
                'pending_confirmation_files' => $account->pending_confirmation_files > 0 ? "Default - {$account->account_number}" : "No open files",
            ];
        });

        return response()->json(['data' => $data]);
    }

    
    /**
     * Get Pending Confirmation Files for the Pending Confirmation Files Table.
     */
    public function getPendingConfirmationFiles()
    {
        // Assuming 'status_id' 3 indicates 'pending_confirmation' in requisitions
        $pendingFiles = FirmAccount::whereHas('requisitions', function ($query) {
            $query->where('status_id', 3);
        })->with('requisitions', 'institution')->get();

        $data = $pendingFiles->map(function ($account) {
            $requisition = $account->requisitions()->where('status_id', 3)->first();
            return [
                'display' => $account->display,
                'file_name' => "Default - {$account->account_number}",
                'payments' => $requisition ? $requisition->payments()->count() : 0,
                'date_generated' => $requisition ? $requisition->created_at->format('d M Y H:i') : '',
                'total_amount' => $requisition ? $requisition->calculateTransactionValue() : 0,
                'status' => 'Pending',
            ];
        });

        return response()->json(['data' => $data]);
    }

    /**
     * Get Recently Closed Files for the Recently Closed Files Table.
     */
    public function getRecentlyClosedFiles(Request $request)
    {
        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        // Assuming 'status_id' 6 represents 'closed' in requisitions
        $closedFiles = FirmAccount::whereHas('requisitions', function ($query) use ($fromDate, $toDate) {
            $query->where('status_id', 6)
                  ->whereBetween('updated_at', [$fromDate, $toDate]);
        })->with('requisitions', 'institution')->get();

        $data = $closedFiles->map(function ($account) {
            $requisition = $account->requisitions()->where('status_id', 6)->first();
            return [
                'display' => $account->display,
                'file_name' => "Default - {$account->account_number}",
                'payments' => $requisition ? $requisition->payments()->count() : 0,
                'date_completed' => $requisition ? $requisition->updated_at->format('d M Y') : '',
                'total_amount' => $requisition ? $requisition->calculateTransactionValue() : 0,
                'status' => 'Closed',
            ];
        });

        return response()->json(['data' => $data]);
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
    public function show(FirmAccount $firmAccount)
    {
        // Return the firmAccount with any required relationships, such as the user who created it
        return response()->json($firmAccount->load('institution','deposits.user','payments.user','payments.accounttype', 'payments.institution'));
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FirmAccount $firmAccount)
    {
        //
    }
}
