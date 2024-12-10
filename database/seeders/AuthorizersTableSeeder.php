<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Authorizer;

class AuthorizersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $authorizers = [
            ['firm_account_id' => 1, 'beneficiary_account_id' => null, 'user_id' => 1],
            ['firm_account_id' => 2, 'beneficiary_account_id' => null, 'user_id' => 1],
            ['firm_account_id' => 3, 'beneficiary_account_id' => null, 'user_id' => 1],
            ['firm_account_id' => 4, 'beneficiary_account_id' => null, 'user_id' => 1],
            ['firm_account_id' => 5, 'beneficiary_account_id' => null, 'user_id' => 1],
            ['firm_account_id' => 6, 'beneficiary_account_id' => null, 'user_id' => 1],
            ['firm_account_id' => 7, 'beneficiary_account_id' => null, 'user_id' => 1],
        ];

        foreach ($authorizers as $authorizer) {
            Authorizer::create($authorizer);
        }
    }
}
