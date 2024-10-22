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
            ['name' => 'ABSA Bank', 'short_name' => 'ABSA'],
            ['name' => 'African Bank', 'short_name' => 'African'],
            ['name' => 'First National Bank', 'short_name' => 'FNB'],
            ['name' => 'Standard Bank', 'short_name' => 'Standard'],
            ['name' => 'Nedbank', 'short_name' => 'Nedbank'],
            ['name' => 'Capitec Bank', 'short_name' => 'Capitec'],
            ['name' => 'Capitec Business', 'short_name' => 'Capitec Business'],
            ['name' => 'Discovery Bank', 'short_name' => 'Discovery'],
            ['name' => 'Investec Bank', 'short_name' => 'Investec'],
            ['name' => 'Tyme Bank', 'short_name' => 'Tyme'],
        ];

        foreach ($institutions as $institution) {
            Institution::create($institution);
        }
    }
}
