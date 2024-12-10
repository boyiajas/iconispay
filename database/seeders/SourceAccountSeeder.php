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
                'display_text' => 'STRAUSS DALY INCORPORATED TRUST',
                'account_holder_type' => 'juristic',//juristic
                'category_id' => 14,
                'account_holder' => 'STRAUSS DALY TRUST',
                'registration_number' => '1992/006281/21',
                'account_number' => '4043442457',
                'account_type_id' => 8,
                'institution_id' => 1, // Assuming 1 refers to ABSA Bank in the institutions table
                'branch_code' => '632005',
                'branch_name' => 'ABSA NORTH DBN GR',
                'number_of_authorizer' => 1,
                'aggregated' => true,
                'authorised' => true,
                'mandated' => true,
                'user_id' => 2
            ],
            [
                'display_text' => 'STRAUSS DALY INCORPORATED T',
                'account_holder_type' => 'juristic',//juristic
                'category_id' => 14,
                'account_holder' => 'STRAUSS DALY T',
                'registration_number' => '1992/006281/21',
                'account_number' => '4053763681',
                'account_type_id' => 8,
                'institution_id' => 1, // Assuming 2 refers to FNB in the institutions table
                'branch_code' => '632005',
                'branch_name' => 'ABSA NORTH DBN GR',
                'swift_code' => 'ABSAZAJJ',
                'number_of_authorizer' => 1,
                'aggregated' => true,
                'authorised' => true,
                'mandated' => true,
                'user_id' => 2
            ],
            [
                'display_text' => 'STRAUSS DALY INCORPORATED STD',
                'account_holder_type' => 'juristic',//juristic
                'category_id' => 14,
                'account_holder' => 'TRUST ACCOUNT',
                'registration_number' => '1992/006281/21',
                'account_number' => '050220748',
                'account_type_id' => 9,
                'institution_id' => 4, // Assuming 3 refers to Standard Bank
                'branch_code' => '040026',
                'branch_name' => 'KINGSMEAD',
                'swift_code' => 'SBZAZAJJ',
                'number_of_authorizer' => 1,
                'aggregated' => true,
                'authorised' => true,
                'mandated' => true,
                'user_id' => 2
            ],
            [
                'display_text' => 'STRAUSS DALY INCORPORATED STD 2',
                'account_holder_type' => 'juristic',//juristic
                'category_id' => 14,
                'account_holder' => 'TRUST ACCOUNT',
                'registration_number' => '1992/006281/21',
                'account_number' => '332654885',
                'account_type_id' => 9,
                'institution_id' => 4, // Assuming 3 refers to Standard Bank
                'branch_code' => '018105',
                'branch_name' => 'SANDTON CITY',
                'swift_code' => 'SBZAZAJJ',
                'number_of_authorizer' => 1,
                'aggregated' => true,
                'authorised' => true,
                'mandated' => true,
                'user_id' => 2
            ],
            [
                'display_text' => 'STRAUSS DALY INC',
                'account_holder_type' => 'juristic',//juristic
                'category_id' => 14,
                'account_holder' => 'STRAUSS DALY INC',
                'registration_number' => '199200628121',
                'account_number' => '1006529101',
                'account_type_id' => 8,
                'institution_id' => 5, // Assuming 1 refers to ABSA Bank
                'branch_code' => '198765',
                'branch_name' => 'SANDTON',
                'swift_code' => 'NEDSZAJJ',
                'number_of_authorizer' => 1,
                'aggregated' => false,
                'authorised' => true,
                'mandated' => true,
                'user_id' => 2
            ],
            [
                'display_text' => 'STRAUSS DALY INCORPORATED NED',
                'account_holder_type' => 'juristic',//juristic
                'category_id' => 14,
                'account_holder' => 'STRAUSS DALY INC',
                'registration_number' => '199200628121',
                'account_number' => '1305724747',
                'account_type_id' => 8,
                'institution_id' => 5, // Assuming 1 refers to ABSA Bank
                'branch_code' => '198765',
                'branch_name' => 'KZN DURBAN',
                'swift_code' => 'NEDSZAJJ',
                'number_of_authorizer' => 1,
                'aggregated' => false,
                'authorised' => true,
                'mandated' => true,
                'user_id' => 2
            ],

            [
                'display_text' => 'STRAUSS DALY INCORPORATED FNB',
                'account_holder_type' => 'juristic',//juristic
                'category_id' => 14,
                'account_holder' => 'STRAUSS DALY INC',
                'registration_number' => '1992/006281/21',
                'account_number' => '62559303244',
                'account_type_id' => 8,
                'institution_id' => 3, // Assuming 1 refers to ABSA Bank
                'branch_code' => '220629',
                'branch_name' => 'UMHLANGA CRESCENT 501',
                'swift_code' => 'FIRNZAJJ',
                'number_of_authorizer' => 1,
                'aggregated' => false,
                'authorised' => true,
                'mandated' => true,
                'user_id' => 2
            ],

            [
                'display_text' => 'STRAUSS DALY INCORPORATED FNB 2',
                'account_holder_type' => 'juristic',//juristic
                'category_id' => 14,
                'account_holder' => 'STRAUSS DALY INC',
                'registration_number' => '1992/006281/21',
                'account_number' => '62161858512',
                'account_type_id' => 8,
                'institution_id' => 3, // Assuming 1 refers to ABSA Bank
                'branch_code' => '220629',
                'branch_name' => 'UMHLANGA CRESCENT 501',
                'swift_code' => 'FIRNZAJJ',
                'number_of_authorizer' => 1,
                'aggregated' => false,
                'authorised' => false,
                'mandated' => true,
                'user_id' => 2
            ],
            
        ];

        foreach ($sourceAccounts as $account) {
            FirmAccount::create($account);
        }
    }
}
