<?php

namespace Database\Seeders;

use App\Models\AccountType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccountTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Unknown'],
            ['name' => 'Cheque account'],
            ['name' => 'Savings account'],
            ['name' => 'Transmission account'],
            ['name' => 'Bond account'],
            ['name' => 'Credit card'],
            ['name' => 'Subscription share'],
            ['name' => 'Trust'],
            ['name' => 'Attorneys Trust Account'],
        ];

        foreach ($categories as $category) {
            AccountType::create($category);
        }
    }
}
