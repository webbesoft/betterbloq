<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Role::create(['name' => 'Super Admin']);

        $adminUser = User::where('email', 'like', '%betterbloq.com')->first();
        if ($adminUser) {
            $superAdminRole = Role::findByName('Super Admin');
            $adminUser->assignRole($superAdminRole);
        }
    }
}
