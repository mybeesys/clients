<?php

namespace Modules\Company\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Administration\Models\Plan;
use Modules\Administration\Models\Feature;

class PlanFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plan1 = Plan::create([
            'name' => 'Silver',
            'description' => 'Primary plan',
            'price' => 29.99,
            'duration' => '30',
        ]);

        $plan2 = Plan::create([
            'name' => 'Gold',
            'description' => 'Primary plan',
            'price' => 49.99,
            'duration' => '40',
        ]);

        // Create features
        $feature1 = Feature::create([
            'name' => 'num_branches',
            'description' => 'number of branches that can the subscriber create',
            'active' => 0,
        ]);


        $plan1->features()->attach([
            $feature1->id => ['value' => '3'],
        ]);

        $plan2->features()->attach([
            $feature1->id => ['value' => '5'],
        ]);
    }
}
