<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now(); 
        $categories = [
            [
                'name' => 'First Aid & Medical',
                'description' => 'Trauma kits, bandages, stretchers, defibrillators, splints.',
                'sku_prefix' => 'MED',
                'created_at' => $now, 
                'updated_at' => $now  
            ],
            [
                'name' => 'Rescue Tools',
                'description' => 'Jaws of life, axes, carabiners, ropes, pulleys, harnesses.',
                'sku_prefix' => 'TOOL',
                'created_at' => $now, 
                'updated_at' => $now  
            ],
            [
                'name' => 'Protective Gear',
                'description' => 'Helmets, gloves, fire-resistant clothing, safety goggles, boots.',
                'sku_prefix' => 'GEAR',
                'created_at' => $now, 
                'updated_at' => $now  
            ],
            [
                'name' => 'Communication',
                'description' => 'Two-way radios, satellite phones, walkie-talkies, headsets.',
                'sku_prefix' => 'COMM',
                'created_at' => $now, 
                'updated_at' => $now  
            ],
            [
                'name' => 'Navigation & Lighting',
                'description' => 'GPS devices, compasses, headlamps, flashlights, floodlights.',
                'sku_prefix' => 'NAV',
                'created_at' => $now, 
                'updated_at' => $now  
            ],
            [
                'name' => 'Survival Support',
                'description' => 'Thermal blankets, emergency rations, water purification, flares.',
                'sku_prefix' => 'SURV',
                'created_at' => $now, 
                'updated_at' => $now  
            ],
        ];

        DB::table('categories')->insert($categories);
    }
}
