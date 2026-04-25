<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class SalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data if needed
        // DB::table('sale_items')->truncate();
        // DB::table('payments')->truncate();
        // DB::table('sales')->truncate();

        $userId = 1; // User who processed the sales
        $now = Carbon::now();

        // Get all products with their prices
        $products = DB::table('products')
            ->join('product_prices', 'products.id', '=', 'product_prices.product_id')
            ->select('products.id', 'products.sku', 'products.name', 'product_prices.retail_price')
            ->get()
            ->keyBy('id');

        // Define different customer profiles
        $customerNames = [
            'Juan Dela Cruz', 'Maria Santos', 'Pedro Reyes', 'Ana Lim', 'Robert Garcia',
            'Elizabeth Tan', 'Michael Sy', 'Susan Ong', 'James Chua', 'Jennifer Lee',
            'David Wong', 'Sarah Chen', 'Daniel Liu', 'Megan Zhang', 'Kevin Huang',
            'Lisa Wang', 'Christopher Zhao', 'Amanda Lin', 'Brian Sun', 'Nicole Wu',
            'Mark Thompson', 'Karen Miller', 'Steven Davis', 'Michelle Wilson',
            'Contractor Pro Builders', 'Home Improvement Co.', 'DIY Construction',
            'City Renovators Ltd.', 'Quick Fix Services', 'Master Builders Inc.'
        ];

        $customerContacts = [
            '09171234567', '09221234567', '09331234567', '09441234567', '09551234567',
            '09661234567', '09771234567', '09881234567', '09991234567', '09001234567',
            '09182345678', '09292345678', '09303456789', '09414567890', '09525678901',
            '09636789012', '09747890123', '09858901234', '09969012345', '09070123456'
        ];

        // Payment methods with probabilities (Cash 60%, GCash 25%, Card 15%)
        $paymentMethods = [
            'Cash', 'Cash', 'Cash', 'Cash', 'Cash', 'Cash',
            'GCash', 'GCash', 'GCash', 'GCash', 'GCash',
            'Card', 'Card', 'Card'
        ];

        // Define peak hours (more sales)
        $peakHours = [9, 10, 11, 14, 15, 16]; // 9-11 AM, 2-4 PM
        $normalHours = [8, 12, 13, 17]; // 8 AM, 12-1 PM, 5 PM

        // Generate sales for November 2025 (1-30, excluding Sundays)
        $salesData = [];
        $saleItemsData = [];
        $paymentsData = [];

        $saleId = 1;
        $totalSales = 0;

        for ($day = 1; $day <= 30; $day++) {
            // Skip Sundays (November 2, 9, 16, 23, 30 are Sundays in 2025)
            $date = Carbon::create(2025, 11, $day);
            if ($date->dayOfWeek === Carbon::SUNDAY) {
                continue;
            }

            // Determine number of sales for this day (more on Saturdays)
            if ($date->dayOfWeek === Carbon::SATURDAY) {
                $salesCount = rand(25, 40); // Busier on Saturdays
            } else {
                $salesCount = rand(15, 30); // Regular days
            }

            for ($saleNum = 1; $saleNum <= $salesCount; $saleNum++) {
                // Determine sale time
                if (in_array($date->dayOfWeek, [Carbon::SATURDAY]) || rand(1, 100) <= 60) {
                    // Peak day or 60% chance of peak hour
                    $hour = $peakHours[array_rand($peakHours)];
                } else {
                    $hour = $normalHours[array_rand($normalHours)];
                }
                
                $minute = rand(0, 59);
                $saleDateTime = Carbon::create(2025, 11, $day, $hour, $minute, 0);

                // Randomly select customer (some sales are walk-ins without customer info)
                $hasCustomerInfo = rand(1, 100) <= 70; // 70% of sales have customer info
                $customerName = $hasCustomerInfo ? $customerNames[array_rand($customerNames)] : null;
                $customerContact = $hasCustomerInfo ? $customerContacts[array_rand($customerContacts)] : null;

                // Create sale record
                $salesData[] = [
                    'id' => $saleId,
                    'sale_date' => $saleDateTime,
                    'user_id' => $userId,
                    'customer_name' => $customerName,
                    'customer_contact' => $customerContact,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                // Generate sale items (1-8 items per sale)
                $itemsCount = rand(1, 8);
                $saleSubtotal = 0;

                // Select products for this sale
                $selectedProductIds = array_rand($products->toArray(), min($itemsCount, count($products)));
                if (!is_array($selectedProductIds)) {
                    $selectedProductIds = [$selectedProductIds];
                }

                foreach ($selectedProductIds as $productId) {
                    $product = $products[$productId];
                    $quantity = $this->getRealisticQuantity($product->sku);
                    $unitPrice = $product->retail_price;
                    $itemTotal = $quantity * $unitPrice;

                    $saleItemsData[] = [
                        'sale_id' => $saleId,
                        'product_id' => $productId,
                        'quantity_sold' => $quantity,
                        'unit_price' => $unitPrice,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    $saleSubtotal += $itemTotal;
                }

                // Create payment record
                $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
                $amountTendered = $this->calculateAmountTendered($saleSubtotal, $paymentMethod);
                $changeGiven = $paymentMethod === 'Cash' ? $amountTendered - $saleSubtotal : 0;

                $paymentsData[] = [
                    'sale_id' => $saleId,
                    'payment_date' => $saleDateTime,
                    'payment_method' => $paymentMethod,
                    'amount_tendered' => $amountTendered,
                    'change_given' => $changeGiven,
                    'reference_no' => $this->generateReferenceNo($paymentMethod),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $saleId++;
                $totalSales++;
            }

            // Show progress
            if ($day % 5 === 0) {
                $this->command->info("Generated sales for November {$day}, 2025...");
            }
        }

        // Insert data in batches
        $this->command->info("Inserting sales data...");
        foreach (array_chunk($salesData, 100) as $chunk) {
            DB::table('sales')->insert($chunk);
        }

        $this->command->info("Inserting sale items data...");
        foreach (array_chunk($saleItemsData, 200) as $chunk) {
            DB::table('sale_items')->insert($chunk);
        }

        $this->command->info("Inserting payments data...");
        foreach (array_chunk($paymentsData, 100) as $chunk) {
            DB::table('payments')->insert($chunk);
        }

        // Update product stock quantities
        $this->updateProductStock($saleItemsData);

        $this->command->info("✅ Successfully generated {$totalSales} sales for November 2025!");
        $this->command->info("📅 Date range: November 1-30, 2025 (excluding Sundays)");
        $this->command->info("🕒 Sale times: 8:00 AM - 5:00 PM");
        $this->command->info("👤 Processed by: User ID {$userId}");
    }

    /**
     * Get realistic quantity for product based on its category/type
     */
    private function getRealisticQuantity(string $sku): int
    {
        $prefix = explode('-', $sku)[0];
        
        switch ($prefix) {
            case 'FSTNR': // Fasteners - sold in bulk
                return rand(10, 100);
            case 'CHEM': // Chemicals - usually 1-3 items
                return rand(1, 3);
            case 'PNT': // Paint - usually 1-5 items
                return rand(1, 5);
            case 'LMBR': // Lumber - usually 1-10 pieces
                return rand(1, 10);
            case 'SAFE': // Safety - usually 1-5 items
                return rand(1, 5);
            case 'PWRTL': // Power tools - usually 1 item
                return 1;
            case 'HNDTL': // Hand tools - usually 1-2 items
                return rand(1, 2);
            default: // Other categories
                return rand(1, 10);
        }
    }

    /**
     * Calculate amount tendered based on payment method
     */
    private function calculateAmountTendered(float $subtotal, string $paymentMethod): float
    {
        if ($paymentMethod === 'Cash') {
            // For cash, round up to nearest 10, 20, 50, or 100
            $roundingOptions = [10, 20, 50, 100];
            $roundTo = $roundingOptions[array_rand($roundingOptions)];
            $rounded = ceil($subtotal / $roundTo) * $roundTo;
            
            // Sometimes give exact change
            if (rand(1, 100) <= 30) {
                return $subtotal;
            }
            
            return $rounded;
        }
        
        // For GCash and Card, usually exact amount
        return $subtotal;
    }

    /**
     * Generate reference number for non-cash payments
     */
    private function generateReferenceNo(string $paymentMethod): ?string
    {
        if ($paymentMethod === 'Cash') {
            return null;
        }
        
        $prefix = $paymentMethod === 'GCash' ? 'GC' : 'CRD';
        $date = date('Ymd');
        $random = str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
        
        return "{$prefix}{$date}{$random}";
    }

    /**
     * Update product stock quantities after sales
     */
    private function updateProductStock(array $saleItemsData): void
    {
        $this->command->info("Updating product stock levels...");
        
        // Group sale items by product ID
        $soldQuantities = [];
        foreach ($saleItemsData as $item) {
            $productId = $item['product_id'];
            $quantity = $item['quantity_sold'];
            
            if (!isset($soldQuantities[$productId])) {
                $soldQuantities[$productId] = 0;
            }
            $soldQuantities[$productId] += $quantity;
        }
        
        // Update each product's stock
        foreach ($soldQuantities as $productId => $quantitySold) {
            $product = DB::table('products')->where('id', $productId)->first();
            if ($product) {
                $newQuantity = max(0, $product->quantity_in_stock - $quantitySold);
                
                DB::table('products')
                    ->where('id', $productId)
                    ->update([
                        'quantity_in_stock' => $newQuantity,
                        'updated_at' => Carbon::now(),
                    ]);
                
                // Check if stock is below reorder level
                if ($newQuantity < $product->reorder_level) {
                    $this->command->warn("⚠️  Product #{$productId} ({$product->sku}) is below reorder level: {$newQuantity}/{$product->reorder_level}");
                }
            }
        }
        
        $this->command->info("✅ Product stock levels updated!");
    }
}