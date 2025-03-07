<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionsTableSeeder::class,
            RolesTableSeeder::class,
            RolePermissionSeeder::class,
            SourceAccountSeeder::class,
            BeneficiaryAccountSeeder::class,
            InstitutionSeeder::class,
            AuditTrailSeeder::class,
            RequisitionSeeder::class,
            CategoriesTableSeeder::class,
            StatusSeeder::class,
            AccountTypesTableSeeder::class,
            AuthorizersTableSeeder::class,
        ]);
    }
}
