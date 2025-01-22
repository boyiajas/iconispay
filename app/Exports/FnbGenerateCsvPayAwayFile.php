<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class FnbGenerateCsvPayAwayFile implements FromCollection
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Provide the data for the Excel file.
     */
    public function collection(): Collection
    {
        // Extract dynamic values from the $data array
        $date = $this->data['date']; // Format date as DD/MM/YYYY
        $firmAccountNumber = $this->data['firmAccountNumber'];
        $tableData = $this->data['tableData'];

        // Determine the number of columns in the table data
        $columnCount = count($tableData[0] ?? []);
        
        // Add the first three rows manually
        $formattedData = collect([
            ['BInSol - U ver 1.00'], // First row
            [$date],          // Second row
            [$firmAccountNumber, '62257431140'],   // Third row split into two columns
            []                      // Empty row before the table
        ]);

        // Add the headers as the fourth row
        $formattedData->push([
            'RECIPIENT NAME',
            'RECIPIENT ACCOUNT',
            'RECIPIENT ACCOUNT TYPE',
            'BRANCHCODE',
            'AMOUNT',
            'OWN REFERENCE',
            'RECIPIENT REFERENCE',
            'EMAIL 1 NOTIFY',
            'EMAIL 1 ADDRESS',
            'EMAIL 1 SUBJECT',
            'EMAIL 2 NOTIFY',
            'EMAIL 2 ADDRESS',
            'EMAIL 2 SUBJECT',
            'EMAIL 3 NOTIFY',
            'EMAIL 3 ADDRESS',
            'EMAIL 3 SUBJECT',
            'EMAIL 4 NOTIFY',
            'EMAIL 4 ADDRESS',
            'EMAIL 4 SUBJECT',
            'EMAIL 5 NOTIFY',
            'EMAIL 5 ADDRESS',
            'EMAIL 5 SUBJECT',
            'FAX 1 NOTIFY',
            'FAX 1 CODE',
            'FAX 1 NUMBER',
            'FAX 1 SUBJECT',
            'FAX 2 NOTIFY',
            'FAX 2 CODE',
            'FAX 2 NUMBER',
            'FAX 2 SUBJECT',
            'SMS 1 NOTIFY',
            'SMS 1 CODE',
            'SMS 1 NUMBER',
            'SMS 2 NOTIFY',
            'SMS 2 CODE',
            'SMS 2 NUMBER',
        ]);

        // Add the main data
        foreach ($tableData as $row) {
            $formattedData->push($row);
        }

        return $formattedData;
    }
}
