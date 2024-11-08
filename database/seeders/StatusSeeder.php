<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            'Open',
            'Incomplete',
            'Awaiting Authorisation',
            'Awaiting Funding',
            'Pending Payment',
            'Pending Payment Confirmation',
            'Payment Processed',
            'Payment Failed',
            'Settled Today',
            'Settled Yesterday',
            'Settled in the Last Week',
            'Settlement Failed / Partially Failed'
        ];

        foreach ($statuses as $status) {
            Status::create(['name' => $status]);
        }
    }
}
