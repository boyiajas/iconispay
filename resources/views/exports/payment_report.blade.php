<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Report Preview</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 16px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <h1>Payment Report Preview</h1>
    <table>
        <thead>
            <tr>
              <th>File Reference</th>
              <th>Amount</th>
              <th>Date Exported</th>
              <th>Date Paid</th>
              <th>To Account Number</th>
              <th>To Payment Institution Name</th>
              <th>To Branch Code</th>
              <th>To Account Type</th>
              <th>To Account Category</th>
              <th>To Accountholder Initials</th>
              <th>To Accountholder Name</th>
              <th>To Accountholder Id Number</th>
              <th>From Account Number</th>
              <th>From Payment Institution Name</th>
              <th>From Branch Code</th>
              <th>From Account Type</th>
              <th>From Accountholder Initials</th>
              <th>From Accountholder Name</th>
              <th>From Accountholder Id Number</th>
              <th>Party Description</th>
              <th>Property Description</th>
              <th>Matter Reason</th>
              <th>Payment Entry Description</th>
              <th>Payment Entry Reference Number</th>
              <th>My Reference</th>
              <th>Recipient Reference</th>
              <th>Authorisers</th>
              <th>Funders</th>
            </tr>
        </thead>
        <tbody>
          @foreach ($firmAccounts as $account)
              @if (isset($account->requisitions) && $account->requisitions->isNotEmpty())
                  @foreach ($account->requisitions as $requisition)
                      @if (isset($requisition->payments) && $requisition->payments->isNotEmpty())
                          @foreach ($requisition->payments as $payment)
                              @php
                                  $payToAccount = $payment->payToAccount ?? null;
                                  $sourceFirmAccount = $payment->sourceFirmAccount ?? null;
                              @endphp
                              <tr>
                                  <td>{{ $requisition->file_reference }}</td>
                                  <td>{{ number_format($payment->amount, 2) }}</td>
                                  <td>{{ $payment->date_exported ? \Carbon\Carbon::parse($payment->date_exported)->format('Y/m/d') : 'N/A' }}</td>
                                  <td>{{ $payment->date_paid ? \Carbon\Carbon::parse($payment->date_exported)->format('Y/m/d') : 'N/A' }}</td>
                                  <td>{{ $payToAccount->account_number ?? 'N/A' }}</td>
                                  <td>{{ $payToAccount->institution->name ?? 'N/A' }}</td>
                                  <td>{{ $payToAccount->branch_code ?? 'N/A' }}</td>
                                  <td>{{ $payToAccount->accountType->name ?? 'N/A' }}</td>
                                  <td>{{ $payToAccount->category->name ?? 'N/A' }}</td>
                                  <td>{{ $payToAccount->initials ?? 'N/A' }}</td>
                                  <td>{{ $payToAccount->company_name ?? $payToAccount->surname ?? 'N/A' }}</td>
                                  <td>{{ $payToAccount->id_number ?? 'N/A' }}</td>
                                  <td>{{ $sourceFirmAccount->account_number ?? 'N/A' }}</td>
                                  <td>{{ $sourceFirmAccount->institution->name ?? 'N/A' }}</td>
                                  <td>{{ $sourceFirmAccount->branch_code ?? 'N/A' }}</td>
                                  <td>{{ $sourceFirmAccount->accountType->name ?? 'N/A' }}</td>
                                  <td>{{ $sourceFirmAccount->initials ?? 'N/A' }}</td>
                                  <td>{{ $sourceFirmAccount->company_name ?? $sourceFirmAccount->surname ?? 'N/A' }}</td>
                                  <td>{{ $sourceFirmAccount->id_number ?? 'N/A' }}</td>
                                  <td>{{ $requisition->parties ?? 'N/A' }}</td>
                                  <td>{{ $requisition->properties ?? 'N/A' }}</td>
                                  <td>{{ $requisition->reason ?? 'N/A' }}</td>
                                  <td>{{ $payment->description ?? 'N/A' }}</td>
                                  <td>{{ $payment->recipient_reference ?? 'N/A' }}</td>
                                  <td>{{ $payment->my_reference ?? 'N/A' }}</td>
                                  <td>{{ $payment->recipient_reference ?? 'N/A' }}</td>
                                  <td>
                                      {{ $requisition->authorizedBy->name ?? 'N/A',}}
                                  </td>
                                  <td>
                                      @if (!empty($requisition->funders))
                                          {{ implode(', ', $requisition->funders->map(fn($f) => $f->name)->toArray()) }}
                                      @else
                                          N/A
                                      @endif
                                  </td>
                              </tr>
                          @endforeach
                      @endif
                  @endforeach
              @endif
          @endforeach
      </tbody>


    </table>
</body>
</html>
