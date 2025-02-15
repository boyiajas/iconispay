<?php

namespace App\Http\Controllers;

use App\Exports\PaidByDateReportExport;
use App\Exports\PaymentReportExport;
use App\Models\BeneficiaryAccount;
use App\Models\FirmAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function generateExcelReport(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'firmAccountId' => 'nullable|integer|exists:firm_accounts,id',
        ]);

        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('superadmin');
        $firmAccountId = $request->firmAccountId;

        // Ensure firm account retrieval respects user role
        $firmAccountQuery = $isSuperAdmin
            ? FirmAccount::query()
            : FirmAccount::where('organisation_id', $user->organisation->id);

        $firmAccount = $firmAccountId ? $firmAccountQuery->find($firmAccountId) : null;

        // Ensure no invalid firm account is accessed
        if ($firmAccountId && !$firmAccount) {
            return response()->json(['message' => 'Unauthorized access or firm account not found'], 403);
        }

        // Clean output buffer
        if (ob_get_contents()) {
            ob_end_clean();
        }

        // Define the file name for the Excel report
        $fileName = 'Paid_Entries_Report_' . now()->format('Ymd_His') . '.xlsx';

        // Generate and return the Excel report
        return Excel::download(new PaymentReportExport($firmAccount), $fileName);
    }


    public function generatePaidByDateReport(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'fromDate' => 'required|date',
            'toDate' => 'required|date|after_or_equal:fromDate',
        ]);

        $fromDate = $request->fromDate;
        $toDate = $request->toDate;

        // Clean output buffer to avoid corruption
        if (ob_get_contents()) {
            ob_end_clean();
        }

        // Define the file name for the Excel report
        $fileName = 'Paid_By_Date_Report_' . now()->format('Ymd_His') . '.xlsx';

        // Generate and return the Excel report
        return Excel::download(new PaidByDateReportExport($fromDate, $toDate), $fileName);
    }


    public function previewPaymentReport()
    { 
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('superadmin');
    
        // Query to fetch firm accounts based on role
        $firmAccountQuery = $isSuperAdmin
            ? FirmAccount::query()
            : FirmAccount::where('organisation_id', $user->organisation->id);
    
        $firmAccounts = $firmAccountQuery->with([
            'requisitions.payments.sourceFirmAccount.institution',
            'requisitions.matter',
        ])->get();
    
        // Enrich payments with payToAccount data
        foreach ($firmAccounts as $firmAccount) {
            foreach ($firmAccount->requisitions as $requisition) {
                foreach ($requisition->payments as $payment) {
                    $payToAccount = match ($payment->account_type) {
                        'B' => BeneficiaryAccount::with(['institution', 'category'])->find($payment->beneficiary_account_id),
                        'F' => FirmAccount::with(['institution', 'category'])->find($payment->beneficiary_account_id),
                        default => null,
                    };
    
                    // Attach payToAccount to the payment object
                    $payment->payToAccount = $payToAccount;
                }
            }
        }
    
        // Return the Blade view for preview
        return view('exports.payment_report', [
            'firmAccounts' => $firmAccounts,
        ]);
    }
    

}
