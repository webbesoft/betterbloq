<?php

namespace Database\Seeders;

use App\Apps\BulkBuy\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        if (app()->environment() === 'local') {
            $categories = [
                [
                    'name' => 'Default',
                ],
            ];

            foreach ($categories as $category) {
                Category::create($category);
            }
        }
    }
}
