<?php

namespace Modules\Administration\Database\Seeders\Tenant;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'username' => 'Test User',

            'email' => 'test@example.com',
            'password' => Hash::make('12345678')
        ]);
    }
}
