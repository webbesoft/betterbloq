<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Role definitions are safe to seed repeatedly. Account privileges are
        // assigned separately through the explicit administrator command.
        Role::findOrCreate('Super Admin', 'web');
    }
}
