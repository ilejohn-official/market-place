<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test buyer
        User::create([
            'name' => 'John Buyer',
            'email' => 'buyer@example.com',
            'password' => Hash::make('password123'),
            'role' => 'buyer',
        ]);

        // Create test seller
        User::create([
            'name' => 'Jane Seller',
            'email' => 'seller@example.com',
            'password' => Hash::make('password123'),
            'role' => 'seller',
        ]);

        // Create additional test users
        User::factory(5)->create(['role' => 'buyer']);
        User::factory(5)->create(['role' => 'seller']);
    }
}
