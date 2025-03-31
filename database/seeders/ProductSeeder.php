<?php

namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        if (app()->environment() === 'local') {
            $products = [
                [
                    'name' => 'Roofing sheets',
                    'vendor_id' => Vendor::first()->pluck('id'),
                ],
            ];

            foreach ($products as $product) {
                Product::create($product);
            }
        }
    }
}
