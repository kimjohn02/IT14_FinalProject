<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now(); 

        $categories = DB::table('categories')->pluck('id', 'sku_prefix')->toArray();
        $default_supplier_id = 1;

        $products = [
            // MED
            [
                'sku' => 'MED-00001',
                'name' => 'Advanced Trauma Kit',
                'description' => 'Comprehensive first aid trauma kit for severe injuries.',
                'category_id' => $categories['MED'] ?? 1,
                'manufacturer_barcode' => null,
                'default_supplier_id' => $default_supplier_id, 
                'quantity_in_stock' => 0,
                'reorder_level' => 10,
            ],
            [
                'sku' => 'MED-00002',
                'name' => 'Portable Defibrillator (AED)',
                'description' => 'Automated external defibrillator, lightweight.',
                'category_id' => $categories['MED'] ?? 1,
                'manufacturer_barcode' => null,
                'default_supplier_id' => $default_supplier_id, 
                'quantity_in_stock' => 0,
                'reorder_level' => 5,
            ],
            
            // TOOL
            [
                'sku' => 'TOOL-00001',
                'name' => 'Hydraulic Rescue Tool (Jaws of Life)',
                'description' => 'Heavy duty hydraulic spreader and cutter.',
                'category_id' => $categories['TOOL'] ?? 2,
                'manufacturer_barcode' => null,
                'default_supplier_id' => 2, 
                'quantity_in_stock' => 0,
                'reorder_level' => 2,
            ],
            [
                'sku' => 'TOOL-00002',
                'name' => 'Static Rescue Rope 11mm (100m)',
                'description' => 'High strength static rope, 11mm diameter, 100 meters.',
                'category_id' => $categories['TOOL'] ?? 2,
                'manufacturer_barcode' => null,
                'default_supplier_id' => 2, 
                'quantity_in_stock' => 0,
                'reorder_level' => 20,
            ],

            // GEAR
            [
                'sku' => 'GEAR-00001',
                'name' => 'Tactical Rescue Helmet',
                'description' => 'Lightweight helmet with headlamp mount and visor.',
                'category_id' => $categories['GEAR'] ?? 3,
                'manufacturer_barcode' => null,
                'default_supplier_id' => 3, 
                'quantity_in_stock' => 0,
                'reorder_level' => 30,
            ],
            [
                'sku' => 'GEAR-00002',
                'name' => 'Extrication Gloves',
                'description' => 'Cut-resistant reinforced extrication gloves (Pair).',
                'category_id' => $categories['GEAR'] ?? 3,
                'manufacturer_barcode' => null,
                'default_supplier_id' => 3, 
                'quantity_in_stock' => 0,
                'reorder_level' => 50,
            ],

            // COMM
            [
                'sku' => 'COMM-00001',
                'name' => 'VHF Two-Way Radio',
                'description' => 'Waterproof VHF handled radio with long battery life.',
                'category_id' => $categories['COMM'] ?? 4,
                'manufacturer_barcode' => null,
                'default_supplier_id' => 2, 
                'quantity_in_stock' => 0,
                'reorder_level' => 15,
            ],
            [
                'sku' => 'COMM-00002',
                'name' => 'Satellite Phone',
                'description' => 'Global coverage satellite phone for remote areas.',
                'category_id' => $categories['COMM'] ?? 4,
                'manufacturer_barcode' => null,
                'default_supplier_id' => 2, 
                'quantity_in_stock' => 0,
                'reorder_level' => 5,
            ],

            // NAV
            [
                'sku' => 'NAV-00001',
                'name' => 'High-Lumen Tactical Headlamp',
                'description' => '1000 lumen waterproof headlamp with strobe.',
                'category_id' => $categories['NAV'] ?? 5,
                'manufacturer_barcode' => null,
                'default_supplier_id' => 3, 
                'quantity_in_stock' => 0,
                'reorder_level' => 40,
            ],
            [
                'sku' => 'NAV-00002',
                'name' => 'Handheld GPS Navigator',
                'description' => 'Rugged handheld GPS with topographic mapping.',
                'category_id' => $categories['NAV'] ?? 5,
                'manufacturer_barcode' => null,
                'default_supplier_id' => 2, 
                'quantity_in_stock' => 0,
                'reorder_level' => 10,
            ],

            // SURV
            [
                'sku' => 'SURV-00001',
                'name' => 'Emergency Thermal Blanket',
                'description' => 'Mylar thermal blanket, retains 90% body heat.',
                'category_id' => $categories['SURV'] ?? 6,
                'manufacturer_barcode' => null,
                'default_supplier_id' => 1, 
                'quantity_in_stock' => 0,
                'reorder_level' => 100,
            ],
            [
                'sku' => 'SURV-00002',
                'name' => 'Emergency Road Flare (3-pack)',
                'description' => 'LED emergency flares, highly visible.',
                'category_id' => $categories['SURV'] ?? 6,
                'manufacturer_barcode' => null,
                'default_supplier_id' => 3, 
                'quantity_in_stock' => 0,
                'reorder_level' => 50,
            ],
        ];

        $products = array_map(function ($product) use ($now) {
            $product['image_path'] = null;
            $product['is_active'] = true;
            $product['date_disabled'] = null;
            $product['disabled_by_user_id'] = null;
            $product['created_at'] = $now;
            $product['updated_at'] = $now;
            return $product;
        }, $products);

        DB::table('products')->insert($products);

        $this->createStockInRecords();
    }

    private function createStockInRecords(): void
    {
        $now = Carbon::now();
        $received_by_user_id = 1; 

        $productIds = DB::table('products')->pluck('id', 'sku')->toArray();

        $stockInData = [
            'MED-00001' => [50, 4500.00, 6500.00, 1],
            'MED-00002' => [10, 35000.00, 45000.00, 1],
            'TOOL-00001' => [5, 120000.00, 150000.00, 2],
            'TOOL-00002' => [100, 6500.00, 9500.00, 2],
            'GEAR-00001' => [80, 2500.00, 4500.00, 3],
            'GEAR-00002' => [200, 850.00, 1500.00, 3],
            'COMM-00001' => [60, 5500.00, 8500.00, 2],
            'COMM-00002' => [15, 45000.00, 65000.00, 2],
            'NAV-00001' => [150, 1200.00, 2500.00, 3],
            'NAV-00002' => [40, 12500.00, 18500.00, 2],
            'SURV-00001' => [500, 85.00, 250.00, 1],
            'SURV-00002' => [300, 450.00, 900.00, 3],
        ];

        $stockInId = DB::table('stock_ins')->insertGetId([
            'stock_in_date' => $now,
            'reference_no' => 'SEED-001',
            'received_by_user_id' => $received_by_user_id,
            'status' => 'completed',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ($stockInData as $sku => $data) {
            list($quantity, $unitCost, $retailPrice, $supplierId) = $data;
            if(!isset($productIds[$sku])) continue;
            $productId = $productIds[$sku];

            DB::table('stock_in_items')->insert([
                'stock_in_id' => $stockInId,
                'product_id' => $productId,
                'supplier_id' => $supplierId,  
                'quantity_received' => $quantity,
                'actual_unit_cost' => $unitCost,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('product_prices')->insert([
                'product_id' => $productId,
                'retail_price' => $retailPrice,
                'stock_in_id' => $stockInId,
                'updated_by_user_id' => $received_by_user_id, 
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('products')
                ->where('id', $productId)
                ->update([
                    'quantity_in_stock' => $quantity,
                    'latest_unit_cost' => $unitCost, 
                    'updated_at' => $now,
                ]);
        }
    }
}