<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BeneficiaryAccount;

class BeneficiaryAccountSeeder extends Seeder
{
    public function run()
    {
        $beneficiaryAccounts = [
            // Juristic account
            [
                'category_id' => 2,
                'account_holder_type' => 'juristic',
                'display_text' => 'ABSA Business Account',
                'company_name' => 'LEGAL ASSOC ATTORNEYS',
                'registration_number' => '2021/123456/07',
                'account_number' => '09734579304',
                'account_type_id' => 2,
                'institution_id' => 1, // Assuming 1 refers to ABSA Bank
                'branch_code' => '543453',
                'authorised' => true,
                'verified' => true,
                'verification_status' => 'successful',
                'account_found' => true,
                'account_open' => true,
                'account_type_verified' => 'Cheque account',
                'account_type_match' => true,
                'branch_code_match' => true,
                'holder_name_match' => true,
                'registration_number_match' => true,
                'user_id' => 1, // Assuming user ID 1 exists
            ],

            // Natural account
            [
                'category_id' => 2,
                'account_holder_type' => 'natural',
                'display_text' => 'Bob\'s Wood Borer Experts',
                'initials' => 'B',
                'surname' => 'WOOD',
                'id_number' => '8901234567089',
                'account_number' => '4657645646',
                'account_type_id' => 4,
                'institution_id' => 2, // Assuming 2 refers to FNB
                'branch_code' => '44450',
                'authorised' => true,
                'verified' => true,
                'verification_status' => 'successful',
                'account_found' => true,
                'account_open' => true,
                'account_type_verified' => 'Cheque account',
                'account_type_match' => true,
                'branch_code_match' => true,
                'holder_name_match' => true,
                'registration_number_match' => false,
                'user_id' => 2, // Assuming user ID 2 exists
            ],

            // Juristic account
            [
                'category_id' => 3,
                'account_holder_type' => 'juristic',
                'display_text' => 'Buffalo City rates',
                'company_name' => 'Buffalo City Municipality',
                'registration_number' => '2002/458967/11',
                'account_number' => '616848819',
                'account_type_id' => 2,
                'institution_id' => 3, // Assuming 3 refers to Standard Bank
                'authorised' => false,
                'verified' => false,
                'verification_status' => 'pending',
                'account_found' => false,
                'account_open' => false,
                'branch_code' => '250655',
                'account_type_verified' => null,
                'account_type_match' => false,
                'branch_code_match' => false,
                'holder_name_match' => false,
                'registration_number_match' => false,
                'user_id' => 3, // Assuming user ID 3 exists
            ],

            // Natural account
            [
                'category_id' => 3,
                'account_holder_type' => 'natural',
                'display_text' => 'John Doe Savings Account',
                'initials' => 'J',
                'surname' => 'DOE',
                'id_number' => '7505051234081',
                'account_number' => '003456789',
                'account_type_id' => 3,
                'institution_id' => 3, // Assuming 3 refers to Standard Bank
                'branch_code' => '66001',
                'authorised' => true,
                'verified' => true,
                'verification_status' => 'successful',
                'account_found' => true,
                'account_open' => true,
                'account_type_verified' => 'Savings account',
                'account_type_match' => true,
                'branch_code_match' => true,
                'holder_name_match' => true,
                'registration_number_match' => false,
                'user_id' => 4, // Assuming user ID 4 exists
            ]
        ];

        foreach ($beneficiaryAccounts as $account) {
            BeneficiaryAccount::create($account);
        }
    }
}
