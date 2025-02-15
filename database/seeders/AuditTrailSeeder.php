<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AuditTrail;
use Illuminate\Support\Facades\DB;

class AuditTrailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate the table first to avoid duplicate entries when running the seeder multiple times
        DB::table('audit_trails')->truncate();

        // Create 10 sample audit trail entries
        $auditTrails = [
           
        ];

        foreach ($auditTrails as $auditTrail) {
            AuditTrail::create($auditTrail);
        }
    }
}
