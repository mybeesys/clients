<?php

namespace Modules\Administration\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Administration\Models\PaymentChannel;

class PaymentChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (PaymentChannel::$classes as $index => $class) {

            PaymentChannel::updateOrCreate(
                ['class_name' => $class],
                [
                    'title' => $class,
                    'class_name' => $class,
                    'status' => 'Active',
                    'image' => null,
                    'settings' => '',
                    'created_at' => time()
                ]
            );
        }
    }
}
