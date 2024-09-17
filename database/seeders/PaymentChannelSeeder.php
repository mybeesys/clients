<?php

namespace Database\Seeders;

use App\Models\PaymentChannel;
use Illuminate\Database\Seeder;

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
