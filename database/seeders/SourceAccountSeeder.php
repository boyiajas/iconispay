<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FirmAccount; // Assuming Source Accounts use the FirmAccount model

class SourceAccountSeeder extends Seeder
{
    public function run()
    {
        $sourceAccounts = [
            [
                'display' => 'Legal Assoc Absa Trust Account',
                'account_holder_type' => 'natural',//juristic
                'category_id' => 4,
                'account_holder' => 'Legal Associates',
                'account_number' => '8078393939',
                'account_type_id' => 3,
                'institution_id' => 1, // Assuming 1 refers to ABSA Bank in the institutions table
                'branch_code' => '180050',
                'aggregated' => true,
                'authorised' => true,
                'mandated' => true,
            ],
            [
                'display' => 'Legal Assoc FNB Trust Account',
                'account_holder_type' => 'juristic',//juristic
                'category_id' => 3,
                'account_holder' => 'Legal Assoc',
                'account_number' => '446578856',
                'account_type_id' => 2,
                'institution_id' => 2, // Assuming 2 refers to FNB in the institutions table
                'branch_code' => '787650',
                'aggregated' => true,
                'authorised' => true,
                'mandated' => true,
            ],
            [
                'display' => 'Legal Assoc Standard Bank Trust Account',
                'account_holder_type' => 'juristic',//juristic
                'category_id' => 4,
                'account_holder' => 'Legal Assoc',
                'account_number' => '093554755',
                'account_type_id' => 2,
                'institution_id' => 3, // Assuming 3 refers to Standard Bank
                'branch_code' => '500050',
                'aggregated' => true,
                'authorised' => true,
                'mandated' => true,
            ],
            [
                'display' => 'Legal Assoc Nedbank Trust Account',
                'account_holder_type' => 'juristic',//juristic
                'category_id' => 2,
                'account_holder' => 'Legal Assoc',
                'account_number' => '9876543210',
                'account_type_id' => 4,
                'institution_id' => 4, // Assuming 4 refers to Nedbank
                'branch_code' => '80044',
                'aggregated' => true,
                'authorised' => false,
                'mandated' => false,
            ],
            [
                'display' => 'Legal Assoc ABSA General Account',
                'account_holder_type' => 'natural',//juristic
                'category_id' => 1,
                'account_holder' => 'Legal Associates',
                'account_number' => '8078393999',
                'account_type_id' => 2,
                'institution_id' => 1, // Assuming 1 refers to ABSA Bank
                'branch_code' => '089650',
                'aggregated' => false,
                'authorised' => false,
                'mandated' => true,
            ],
            [
                'display' => 'Legal Assoc FNB Business Account',
                'account_holder_type' => 'natural',//juristic
                'category_id' => 4,
                'account_holder' => 'Legal Associates',
                'account_number' => '446578886',
                'account_type_id' => 4,
                'institution_id' => 2, // Assuming 2 refers to FNB
                'branch_code' => '50650',
                'aggregated' => false,
                'authorised' => true,
                'mandated' => true,
            ],
            [
                'display' => 'Legal Assoc Standard Bank Current Account',
                'account_holder_type' => 'natural',//juristic
                'category_id' => 6,
                'account_holder' => 'Legal Assoc',
                'account_number' => '093554799',
                'account_type_id' => 3,
                'institution_id' => 3, // Assuming 3 refers to Standard Bank
                'branch_code' => '780001',
                'aggregated' => true,
                'authorised' => false,
                'mandated' => false,
            ],
            [
                'display' => 'Legal Assoc Nedbank Savings Account',
                'account_holder_type' => 'natural',//juristic
                'category_id' => 4,
                'account_holder' => 'Legal Associates',
                'account_number' => '6549873210',
                'account_type_id' => 3,
                'institution_id' => 4, // Assuming 4 refers to Nedbank
                'branch_code' => '50150',
                'aggregated' => false,
                'authorised' => true,
                'mandated' => false,
            ],
            [
                'display' => 'Capitec Savings Account',
                'account_holder_type' => 'juristic',//juristic
                'category_id' => 4,
                'account_holder' => 'Legal Associates',
                'account_number' => '6549003210',
                'account_type_id' => 4,
                'institution_id' => 5, // Assuming 4 refers to Nedbank
                'branch_code' => '10150',
                'aggregated' => false,
                'authorised' => true,
                'mandated' => false,
            ],
            [
                'display' => 'Legal Assoc Fnb Cheque Account',
                'account_holder_type' => 'juristic',//juristic
                'category_id' => 3,
                'account_holder' => 'In2assets Property Auctions',
                'account_number' => '3549000210',
                'account_type_id' => 2,
                'institution_id' => 2, // Assuming 4 refers to Nedbank
                'branch_code' => '50050',
                'aggregated' => true,
                'authorised' => true,
                'mandated' => false,
            ],
            [
                'display' => 'Nedbank Savings Account',
                'account_holder_type' => 'natural',//juristic
                'category_id' => 4,
                'account_holder' => 'Legal Associates',
                'account_number' => '6549873210',
                'account_type_id' => 1,
                'institution_id' => 4, // Assuming 4 refers to Nedbank
                'branch_code' => '50150',
                'aggregated' => false,
                'authorised' => false,
                'mandated' => false,
            ],
            [
                'display' => 'Legal Assoc Fnb Bussiness Account',
                'account_holder_type' => 'juristic',//juristic
                'category_id' => 4,
                'account_holder' => 'Xpress Home',
                'account_number' => '6500173210',
                'account_type_id' => 3,
                'institution_id' => 4, // Assuming 4 refers to Nedbank
                'branch_code' => '50150',
                'aggregated' => false,
                'authorised' => true,
                'mandated' => false,
            ]
        ];

        foreach ($sourceAccounts as $account) {
            FirmAccount::create($account);
        }
    }
}
