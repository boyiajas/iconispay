<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin Role
        $adminRole = Role::findByName('admin');
        $adminRole->givePermissionTo(Permission::all());

        // Role: Authoriser (can authorise payments, manage accounts, and authorise users)
        $authoriserRole = Role::findByName('authoriser');
        $authoriserRole->givePermissionTo([
            'authorise payments',
            'manage firm accounts',
            'create authorisers',
            'create bookkeepers',
        ]);

        // Role: Bookkeeper (can generate pay-away files and mark payments as successful)
        $bookkeeperRole = Role::findByName('bookkeeper');
        $bookkeeperRole->givePermissionTo([
            'generate pay-away files',
            'mark payments successful',
        ]);

        // Photographer Role
        $userRole = Role::findByName('user');
        $userRole->givePermissionTo([
            'create payments',
            'create requisition'
        ]);

        $user = \App\Models\User::factory()->create([
            'name' => 'Peter Ajakaiye',
            'email' => 'boyiajas@gmail.com',
            'status' => 'active',
            'password' => bcrypt('gospel123')
        ]);
        $user->assignRole($adminRole);

        $user = \App\Models\User::factory()->create([
            'name' => 'Sicelimpilo Purity',
            'email' => 'scexulu42@gmail.com',
            'status' => 'active',
            'password' => bcrypt('gospel123')
        ]);
        $user->assignRole($authoriserRole);

        $user = \App\Models\User::factory()->create([
            'name' => 'Kenny Paul',
            'email' => 'kennyboy@gmail.com',
            'status' => 'active',
            'password' => bcrypt('gospel123')
        ]);
        $user->assignRole($bookkeeperRole);
        $user->assignRole($userRole);

        $user = \App\Models\User::factory()->create([
            'name' => 'Thobilo Humility',
            'email' => 'humility@gmail.com',
            'status' => 'active',
            'password' => bcrypt('gospel123')
        ]);
        $user->assignRole($userRole);
    }
}
