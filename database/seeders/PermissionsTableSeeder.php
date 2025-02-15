<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Permissions
        Permission::create(['name' => 'authorise payments']);
        Permission::create(['name' => 'manage firm accounts']);
        Permission::create(['name' => 'manage authorisers']);
        Permission::create(['name' => 'manage bookkeepers']);
        Permission::create(['name' => 'generate pay-away files']);
        Permission::create(['name' => 'mark payments successful']);
        Permission::create(['name' => 'manage user logins']);

        // payment permissions
        Permission::create(['name' => 'view payments']);
        Permission::create(['name' => 'create payments']);
        Permission::create(['name' => 'edit payments']);
        Permission::create(['name' => 'delete payments']);

        // User permissions
        Permission::create(['name' => 'view users']);
        Permission::create(['name' => 'create users']);
        Permission::create(['name' => 'edit users']);
        Permission::create(['name' => 'delete users']);

        // authorisers permissions
        Permission::create(['name' => 'view authorisers']);
        Permission::create(['name' => 'create authorisers']);
        Permission::create(['name' => 'edit authorisers']);
        Permission::create(['name' => 'delete authorisers']);

        // bookkeepers permissions
        Permission::create(['name' => 'view bookkeepers']);
        Permission::create(['name' => 'create bookkeepers']);
        Permission::create(['name' => 'edit bookkeepers']);
        Permission::create(['name' => 'delete bookkeepers']);

        Permission::create(['name' => 'view requisition']);
        Permission::create(['name' => 'create requisition']);
        Permission::create(['name' => 'edit requisition']);
        Permission::create(['name' => 'delete requisition']);

        Permission::create(['name' => 'view organisation']);
        Permission::create(['name' => 'create organisation']);
        Permission::create(['name' => 'edit organisation']);
        Permission::create(['name' => 'delete organisation']);

        Permission::create(['name' => 'view certificate']);
        Permission::create(['name' => 'create certificate']);
        Permission::create(['name' => 'edit certificate']);
        Permission::create(['name' => 'delete certificate']);

        Permission::create(['name' => 'view audit']);
        Permission::create(['name' => 'create audit']);
        Permission::create(['name' => 'edit audit']);
        Permission::create(['name' => 'delete audit']);

    }
}
