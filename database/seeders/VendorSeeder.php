<?php

namespace Database\Seeders;

use App\Models\User;
use App\Apps\BulkBuy\Models\Vendor;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        if (app()->environment() === 'local') {
            $vendors = [
                [
                    'name' => 'Default Vendor',
                    'phone' => '+1 (456) 6789',
                    'user_id' => User::first()['id'],
                ],
            ];

            foreach ($vendors as $vendor) {
                Vendor::create($vendor);
            }
        }
    }
}