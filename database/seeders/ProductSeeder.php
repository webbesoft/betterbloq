<?php

namespace Database\Seeders;

use App\Apps\BulkBuy\Models\Vendor;
use App\Apps\BulkBuy\Models\Product;
use App\Apps\BulkBuy\Models\Category;
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
                    'description' => 'test generated product',
                    'image' => 'https://picsum.photos/id/237/200/300',
                    'vendor_id' => Vendor::first()['id'],
                    'price' => 4.99,
                    'unit' => 'each',
                    'category_id' => Category::first()['id'],
                ],
            ];

            foreach ($products as $product) {
                Product::create($product);
            }
        }
    }
}
