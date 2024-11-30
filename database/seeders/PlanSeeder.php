<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use LucasDotVin\Soulbscription\Enums\PeriodicityType;
use LucasDotVin\Soulbscription\Models\Plan;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $silver = Plan::create([
            'name'             => 'silver',
            'periodicity_type' => PeriodicityType::Month,
            'periodicity'      => 1,
            'price' => 1000,
        ]);

        $gold = Plan::create([
            'name'             => 'gold',
            'periodicity_type' => PeriodicityType::Month,
            'periodicity'      => 1,
            'price' => 1000,
        ]);
    }
}
