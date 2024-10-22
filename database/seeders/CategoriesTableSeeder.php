<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => '-- Other --'],
            ['name' => 'Advocate'],
            ['name' => 'Attorney'],
            ['name' => 'Bank'],
            ['name' => 'Body Corporate'],
            ['name' => 'Bond account'],
            ['name' => 'Bridging Finance Company'],
            ['name' => 'Client'],
            ['name' => 'Compliance Certificate Provider'],
            ['name' => 'Creditor'],
            ['name' => 'Estate Agent'],
            ['name' => 'Firm business account'],
            ['name' => 'Firm investment account'],
            ['name' => 'Firm trust account'],
            ['name' => 'Home Owners Association'],
            ['name' => 'Municipality / Government'],
            ['name' => 'Seller / Developer account'],
            ['name' => 'Sheriff'],
            ['name' => 'Staff']
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
