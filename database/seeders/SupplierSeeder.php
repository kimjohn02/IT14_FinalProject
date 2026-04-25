<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now(); 
        
        $suppliers = [
            [
                'supplier_name' => 'LifeLine Medical Responders',
                'contactNO' => '09171112233',
                'address' => '100 Medical Plaza, Metro Manila',
                'is_active' => true,
                'created_at' => $now, 
                'updated_at' => $now  
            ],
            [
                'supplier_name' => 'ResQ-Tech Industries',
                'contactNO' => '09182223344',
                'address' => '55 Tech Park, Cebu City',
                'is_active' => true,
                'created_at' => $now, 
                'updated_at' => $now  
            ],
            [
                'supplier_name' => 'SurvivorGear Pro',
                'contactNO' => '09193334455',
                'address' => '88 Ranger St, Davao City',
                'is_active' => true,
                'created_at' => $now, 
                'updated_at' => $now  
            ],
        ];

        DB::table('suppliers')->insert($suppliers);
    }
}
