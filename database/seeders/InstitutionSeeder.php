<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Institution;

class InstitutionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $institutions = [
            ['name' => 'ABSA Bank', 'short_name' => 'ABSA', 'branch_code' => '632005'],
            ['name' => 'African Bank', 'short_name' => 'African'],
            ['name' => 'First National Bank', 'short_name' => 'FNB', 'branch_code' => '250655'],
            ['name' => 'Standard Bank', 'short_name' => 'Standard', 'branch_code' => '051001'],
            ['name' => 'Nedbank', 'short_name' => 'Nedbank', 'branch_code' => '198765'],
            ['name' => 'Capitec Bank', 'short_name' => 'Capitec', 'branch_code' => '470010'],
            ['name' => 'Capitec Business', 'short_name' => 'Capitec Business', 'branch_code' => '450105'],
            ['name' => 'Discovery Bank', 'short_name' => 'Discovery', 'branch_code' => '679000'],
            ['name' => 'Investec Bank', 'short_name' => 'Investec', 'branch_code' => '580105'],
            ['name' => 'Tyme Bank', 'short_name' => 'Tyme', 'branch_code' => '678910'],
        ];

        foreach ($institutions as $institution) {
            Institution::create($institution);
        }
    }
}
