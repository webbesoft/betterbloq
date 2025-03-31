<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Standard',
                'slug' => 'standard_monthly',
                'stripe_plan' => config('app.plans.standard_monthly.stripe_name'),
                'price' => config('app.plans.standard_monthly.price'),
                'description' => 'Standard plan.',
            ],
            // [
            //     'name' => 'Premium',
            //     'slug' => 'premium',
            //     'stripe_plan' => 'price_1LXP23Gzlk2XAanf4zQZdi',
            //     'price' => 100,
            //     'description' => 'Premium'
            // ]
        ];

        foreach ($plans as $plan) {
            Plan::create($plan);
        }
    }
}
