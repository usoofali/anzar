<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Staff Users
        $manager = User::create([
            'name' => 'Alhaji Aminu Bello Manager',
            'email' => 'manager@anzar.com',
            'username' => 'manager',
            'phone' => '08031234567',
            'role' => 'manager',
            'status' => 'active',
            'password' => Hash::make('password'),
        ]);

        $productionStaff = User::create([
            'name' => 'Production Staff',
            'email' => 'production@anzar.com',
            'username' => 'production',
            'phone' => '08059876543',
            'role' => 'production_staff',
            'status' => 'active',
            'password' => Hash::make('password'),
        ]);

        $salesStaff = User::create([
            'name' => 'Sales Staff',
            'email' => 'sales@anzar.com',
            'username' => 'sales',
            'phone' => '08023456789',
            'role' => 'sales_staff',
            'status' => 'active',
            'password' => Hash::make('password'),
        ]);
    }
}
