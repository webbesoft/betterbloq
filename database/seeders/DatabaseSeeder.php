<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Demo seeding is disabled outside local and testing environments. Seed role definitions explicitly instead.');
        }

        User::factory()->create([
            'name' => 'Base User',
            'email' => 'admin@betterbloq.com',
        ]);

        $this->call([
            PlanSeeder::class,
            RolesAndPermissionsSeeder::class,
            VendorSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
        ]);
    }
}
