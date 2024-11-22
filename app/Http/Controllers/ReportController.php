<?php

namespace App\Http\Controllers;

use App\Exports\PaymentReportExport;
use App\Models\BeneficiaryAccount;
use App\Models\FirmAccount;
use Illuminate\Http\Request;
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

        // Retrieve the firm account
        $firmAccountId = $request->firmAccountId;
        $firmAccount = $firmAccountId ? FirmAccount::find($firmAccountId) : null;

        // Clean output buffer
        if (ob_get_contents()) {
            ob_end_clean();
        }
        // Define the file name for the Excel report
        $fileName = 'Paid_Entries_Report_' . now()->format('Ymd_His') . '.xlsx';
       
        // Use the PaymentReportExport to generate the Excel file and return it for download
        return Excel::download(new PaymentReportExport($firmAccount), $fileName);
    }

    public function previewPaymentReport()
    { 
        // Fetch firm accounts and their related data
        $firmAccounts = FirmAccount::with([
            'requisitions.payments.sourceFirmAccount.institution',
            'requisitions.matter',
        ])->get();

        // Enrich payments with payToAccount data
        foreach ($firmAccounts as $firmAccount) {
            foreach ($firmAccount->requisitions as $requisition) {
                foreach ($requisition->payments as $payment) {
                    if ($payment->account_type === 'B') {
                        $payToAccount = BeneficiaryAccount::with(['institution', 'category'])->find($payment->beneficiary_account_id);
                    } elseif ($payment->account_type === 'F') {
                        $payToAccount = FirmAccount::with(['institution', 'category'])->find($payment->beneficiary_account_id);
                    } else {
                        $payToAccount = null;
                    }

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
