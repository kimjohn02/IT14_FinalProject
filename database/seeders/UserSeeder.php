<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'f_name' => 'Admin',
            'l_name' => 'User',
            'username' => 'admin',
            'email' => 'admin@sarequip.com',
            'role' => 'Administrator',
            'is_active' => true,
            'password' => Hash::make('admin123'),
            'password_changed' => false,
        ]);
        
        User::create([
            'f_name' => 'Cashier',
            'l_name' => 'User',
            'username' => 'cashier',
            'email' => 'cashier@sarequip.com',
            'role' => 'cashier',
            'is_active' => true,
            'password' => Hash::make('cash1234'),
            'password_changed' => false,
        ]);        
    }
}