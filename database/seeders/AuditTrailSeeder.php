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
            [
                'user_name' => 'Brian@LegalAssoc',
                'action' => 'Created Report Result',
                'details' => 'Created Payaway File Paid Entry Report with the filename of `Paid Entries Report for 20180601 - 20180701_20180731T133030.xlsx`',
                'created_at' => '2018-07-31 13:13:30',
            ],
            [
                'user_name' => 'Brian@LegalAssoc',
                'action' => 'Created Report Result',
                'details' => 'Created Payaway File Paid Entry Report with the filename of `Paid Entries Report for 20160101 - 20180731_20180731T31224.xlsx`',
                'created_at' => '2018-07-31 11:24:00',
            ],
            [
                'user_name' => 'Brian@LegalAssoc',
                'action' => 'Created Report Result',
                'details' => 'Created Payaway File Paid Entry Report with the filename of `Paid Entries Report for 20180621 - 20180621_20180621T141515.xlsx`',
                'created_at' => '2018-06-21 15:55:05',
            ],
            [
                'user_name' => '<none>',
                'action' => 'Password Reset',
                'details' => 'Password reset for user with the username `guest@LegalAssoc`',
                'created_at' => '2018-06-11 17:47:37',
            ],
            [
                'user_name' => 'bryan.thomas@training',
                'action' => 'Password Reset Requested',
                'details' => 'Password reset requested by an administrator for user with the username `guest@LegalAssoc`',
                'created_at' => '2018-06-11 17:46:55',
            ],
            [
                'user_name' => 'guest@LegalAssoc',
                'action' => 'Access Failed',
                'details' => 'Incorrect password entered for user with the username `guest@LegalAssoc`',
                'created_at' => '2018-06-11 17:46:46',
            ],
            [
                'user_name' => '<Notification Processor>',
                'action' => 'Email Notification Sent',
                'details' => 'Sent an email notification to bryant@korbitec.com for `FirmAccountAuthorised`',
                'created_at' => '2018-05-24 14:48:05',
            ],
            [
                'user_name' => '<Notification Processor>',
                'action' => 'Created Email Notification Outbox',
                'details' => 'Created an email notification outbox to bryant@korbitec.com and `Firm Account Authorised`',
                'created_at' => '2018-05-24 14:48:05',
            ],
            [
                'user_name' => 'Brian@LegalAssoc',
                'action' => 'Created Firm Account',
                'details' => 'Created a new firm account for Legal Associates with account number `1234567890`.',
                'created_at' => '2018-05-20 14:48:05',
            ],
            [
                'user_name' => 'Michelle@LegalAssoc',
                'action' => 'Created Beneficiary Account',
                'details' => 'Created a new beneficiary account for ABC Corporation with account number `0987654321`.',
                'created_at' => '2018-05-15 10:30:00',
            ]
        ];

        foreach ($auditTrails as $auditTrail) {
            AuditTrail::create($auditTrail);
        }
    }
}
