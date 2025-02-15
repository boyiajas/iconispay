<?php

namespace App\Exports;

use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PaidByDateReportExport implements FromCollection, WithHeadings
{
    protected $fromDate;
    protected $toDate;
    protected $user;
    protected $isSuperAdmin;

    public function __construct($fromDate, $toDate)
    {
        $this->fromDate = Carbon::parse($fromDate)->startOfDay();
        $this->toDate = Carbon::parse($toDate)->endOfDay();
        $this->user = Auth::user();
        $this->isSuperAdmin = $this->user->hasRole('superadmin');
    }

    /**
     * Define the headings for the Excel sheet.
     */
    public function headings(): array
    {
        return [
            'File Reference',
            'Amount',
            'Date Exported',
            'Date Paid',
            'Requisition Created By',
            'Requisition Approved By',
            'To Account Number',
            'To Payment Institution Name',
            'To Branch Code',
            'To Account Type',
            'To Account Category',
            'To Accountholder Initials',
            'To Accountholder Name',
            'To Accountholder Id Number',
            'From Account Number',
            'From Payment Institution Name',
            'From Branch Code',
            'From Account Type',
            'From Accountholder Initials',
            'From Accountholder Name',
            'From Accountholder Id Number',
            'Party Description',
            'Property Description',
            'Matter Reason',
            'Payment Entry Description',
            'Payment Entry Reference Number',
            'My Reference',
            'Recipient Reference',
        ];
    }

    /**
     * Fetch data for the report as a collection.
     */
    public function collection(): Collection
    {
        // Query payments with relationships
        $paymentQuery = Payment::with([
            'sourceFirmAccount.institution',
            'sourceFirmAccount.accountType',
            'beneficiaryAccount.institution',
            'beneficiaryAccount.category',
            'payToFirmAccount.institution',
            'payToFirmAccount.category',
            'requisition.matter',
            'requisition.user', // User who created the requisition
            'requisition.authorizedBy', // User who approved the requisition
            'requisition.fileUploads', // Associated file uploads
        ])
        ->whereHas('requisition', function ($query) {
            $query->whereBetween('completed_at', [$this->fromDate, $this->toDate]);
        });

        // Apply organization restriction if NOT Super Admin
        if (!$this->isSuperAdmin) {
            $paymentQuery->whereHas('sourceFirmAccount', function ($q) {
                $q->where('organisation_id', $this->user->organisation->id);
            });
        }

        $payments = $paymentQuery->get();

        // Prepare data for the report
        $data = $payments->map(function ($payment) {
            // Dynamically load the correct "pay to" account
            $payToAccount = $payment->payToAccount;
            $sourceFirmAccount = $payment->sourceFirmAccount;

            // Get the first `generated_at` date from FileUploads associated with the Requisition
            $dateExported = optional($payment->requisition->fileUploads->first())->generated_at;

            return [
                $payment->requisition->file_reference ?? 'N/A',
                number_format($payment->amount, 2),
                optional($dateExported)->format('Y/m/d') ?? 'N/A', // Mapped `date_exported`
                $payment->date_paid ? Carbon::parse($payment->mark_processed_at)->format('Y/m/d') : 'N/A',
                optional($payment->requisition->user)->name ?? 'N/A',         // Requisition Created By
                optional($payment->requisition->authorizedBy)->name ?? 'N/A', // Requisition Approved By
                $payToAccount->account_number ?? 'N/A',
                $payToAccount->institution->name ?? 'N/A',
                $payToAccount->branch_code ?? 'N/A',
                $payment->account_type ?? 'N/A',
                $payToAccount->category->name ?? 'N/A',
                $payToAccount->initials ?? 'N/A',
                $payToAccount->company_name ?? $payToAccount->surname ?? 'N/A',
                $payToAccount->id_number ?? 'N/A',
                $sourceFirmAccount->account_number ?? 'N/A',
                $sourceFirmAccount->institution->name ?? 'N/A',
                $sourceFirmAccount->branch_code ?? 'N/A',
                $sourceFirmAccount->accountType->name ?? 'N/A',
                $sourceFirmAccount->initials ?? 'N/A',
                $sourceFirmAccount->company_name ?? $sourceFirmAccount->surname ?? 'N/A',
                $sourceFirmAccount->id_number ?? 'N/A',
                $payment->requisition->parties ?? 'N/A',
                $payment->requisition->properties ?? 'N/A',
                $payment->requisition->matter->reason ?? 'N/A',
                $payment->description ?? 'N/A',
                $payment->recipient_reference ?? 'N/A',
                $payment->my_reference ?? 'N/A',
            ];
        });

        return collect($data);
    }
}
