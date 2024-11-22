<?php

namespace App\Exports;

use App\Models\BeneficiaryAccount;
use App\Models\FirmAccount;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithHeadings;

//testing link to view
// http://127.0.0.1:8000/api/report-preview?firmAccountId=14

class PaymentReportExport implements /* FromView, */ FromCollection, WithHeadings
{
    protected $firmAccount;

    public function __construct($firmAccount = null)
    {
        $this->firmAccount = $firmAccount;
    }

    /**
     * Provide headings for the Excel sheet
     */
    public function headings(): array
    {
        return [
            'File Reference',
            'Amount',
            'Date Exported',
            'Date Paid',
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
            'Authorisers',
            'Funders',
        ];
    }

    /**
     * Return data as a collection for Excel export
     */
    public function collection(): Collection
    {
        $query = FirmAccount::with([
            'requisitions.payments.sourceFirmAccount.institution',
            'requisitions.payments.sourceFirmAccount.accountType',
            'requisitions.matter',
        ])
        ->when($this->firmAccount, function ($query) {
            $query->where('id', $this->firmAccount->id);
        })
        ->get();

        $data = [];

        foreach ($query as $firmAccount) {
            foreach ($firmAccount->requisitions as $requisition) {
                foreach ($requisition->payments as $payment) {
                    $payToAccount = $payment->account_type === 'B'
                        ? BeneficiaryAccount::find($payment->beneficiary_account_id)
                        : FirmAccount::find($payment->beneficiary_account_id);

                    $sourceFirmAccount = $payment->sourceFirmAccount;

                    $data[] = [
                        $requisition->file_reference ?? 'N/A',
                        number_format($payment->amount, 2),
                        $payment->date_exported ? Carbon::parse($payment->date_exported)->format('Y/m/d') : 'N/A',
                        $payment->date_paid ? Carbon::parse($payment->date_exported)->format('Y/m/d') : 'N/A',
                        $payToAccount->account_number ?? 'N/A',
                        $payToAccount->institution->name ?? 'N/A',
                        $payToAccount->branch_code ?? 'N/A',
                        $payToAccount->accountType->name ?? 'N/A',
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
                        $requisition->parties ?? 'N/A',
                        $requisition->properties ?? 'N/A',
                        $requisition->reason ?? 'N/A',
                        $payment->description ?? 'N/A',
                        $payment->recipient_reference ?? 'N/A',
                        $payment->my_reference ?? 'N/A',
                        $payment->recipient_reference ?? 'N/A',
                        // Check for authorisers and funders before calling map()
                        $requisition->authorizedBy->name ?? 'N/A',
                        isset($requisition->funders)
                            ? implode(', ', $requisition->funders->map(fn($f) => $f->name)->toArray())
                            : 'N/A',
                    ];
                    
                }
            }
        }
        
        return collect($data);
    }
}
