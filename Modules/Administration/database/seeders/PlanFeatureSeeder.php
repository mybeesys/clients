<?php

namespace Modules\Administration\Database\Seeders;

use Illuminate\Database\Seeder;
use LucasDotVin\Soulbscription\Enums\PeriodicityType;
use Modules\Administration\Models\Plan;
use Modules\Administration\Models\Feature;

class PlanFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $silver = Plan::create([
            'name'             => 'silver',
            'periodicity_type' => PeriodicityType::Month,
            'periodicity'      => 1,
            'description' => 'Primary plan',
            'price' => 29.99,
            'duration' => '30',
            'active'      => 1,

        ]);

        $gold = Plan::create([
            'name'             => 'gold',
            'periodicity_type' => PeriodicityType::Month,
            'periodicity'      => 1,
            'description' => 'Primary plan',
            'price' => 49.99,
            'duration' => '40',
            'active'      => 1,

        ]);


        $feature1 = Feature::create([
            'consumable'       => true,
            'name'             => 'num_branches',
            'description' => 'number of branches that can the subscriber create',
            'active' => 1,
            'periodicity_type' => PeriodicityType::Day,
            'periodicity'      => 1,
            'active'      => 1,

        ]);


        $silver->features()->attach([
            $feature1->id => ['value' => '3', 'charges' => 15],
        ]);


        $gold->features()->attach([
            $feature1->id => ['value' => '5', 'charges' => 25],
        ]);
    }
}
