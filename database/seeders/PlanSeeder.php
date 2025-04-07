<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\PlanLimit;
use App\Models\Project;
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
                'name' => 'Basic',
                'slug' => 'basic_monthly',
                'stripe_plan' => config('app.plans.basic_monthly.stripe_name'),
                'price' => config('app.plans.basic_monthly.price'),
                'description' => 'Basic plan.',
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro_monthly',
                'stripe_plan' => config('app.plans.pro_monthly.stripe_name'),
                'price' => config('app.plans.pro_monthly.price'),
                'description' => 'Pro plan.',
            ],
        ];

        foreach ($plans as $plan) {
            Plan::create($plan);
        }

        $basicPlan = Plan::where('slug', 'like', '%basic%')->first();
        if ($basicPlan) {
            $basicLimits = [
                ['plan_id' => $basicPlan->id, 'model' => Project::class, 'value' => 5],
                ['plan_id' => $basicPlan->id, 'model' => Order::class, 'value' => 50],
                ['plan_id' => $basicPlan->id, 'model' => 'Lot', 'value' => 10],
            ];

            foreach ($basicLimits as $basicLimit) {
                PlanLimit::create($basicLimit);
            }

            $basicFeatures = [
                [
                    'plan_id' => $basicPlan->id,
                    'key' => 'basic.projects',
                    'description' => 'Basic Project Management (5 Projects)',
                ],
                [
                    'plan_id' => $basicPlan->id,
                    'key' => 'basic.bulk-market',
                    'description' => 'Access to Just-in-time Bulk Purchasing (50 orders)',
                ],
                [
                    'plan_id' => $basicPlan->id,
                    'key' => 'basic.lot-analysis',
                    'description' => 'Basic Lot Analysis Tools (10 lots)',
                ],
                [
                    'plan_id' => $basicPlan->id,
                    'key' => 'basic.hiring',
                    'description' => 'Contractor Marketplace Access',
                ],
                [
                    'plan_id' => $basicPlan->id,
                    'key' => 'basic.support',
                    'description' => 'Standard Support',
                ],
            ];

            foreach ($basicFeatures as $basicFeature) {
                PlanFeature::create($basicFeature);
            }
        }

        // Pro Plan Limits
        $proPlan = Plan::where('slug', 'like', '%pro%')->first();
        if ($proPlan) {
            $proLimits = [
                ['plan_id' => $proPlan->id, 'model' => Project::class, 'value' => 50],
                ['plan_id' => $proPlan->id, 'model' => Order::class, 'value' => null],
            ];

            foreach ($proLimits as $proLimit) {
                PlanLimit::create($proLimit);
            }

            $proFeatures = [
                [
                    'plan_id' => $proPlan->id,
                    'key' => 'pro.projects',
                    'description' => 'Advanced Project Management (50 Projects)',
                ],
                [
                    'plan_id' => $proPlan->id,
                    'key' => 'pro.bulk-market',
                    'description' => 'Unlimited Access to Just-in-time Bulk Purchasing',
                ],
                [
                    'plan_id' => $proPlan->id,
                    'key' => 'pro.lot-analysis',
                    'description' => 'Advanced Lot Analysis & Feasability',
                ],
                [
                    'plan_id' => $proPlan->id,
                    'key' => 'pro.plan-management',
                    'description' => 'Unlimited Plan Management',
                ],
                [
                    'plan_id' => $proPlan->id,
                    'key' => 'pro.hiring',
                    'description' => 'Contractor Marketplace Access',
                ],
                [
                    'plan_id' => $proPlan->id,
                    'key' => 'pro.reports',
                    'description' => 'Advanced Reporting',
                ],
                [
                    'plan_id' => $proPlan->id,
                    'key' => 'pro.support',
                    'description' => 'Priority Support',
                ],
            ];

            foreach ($proFeatures as $proFeature) {
                PlanFeature::create($proFeature);
            }
        }
    }
}
