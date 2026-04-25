<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('return_items', function (Blueprint $table) {
            $table->id();

             // --- Foreign Keys (Linking to Primary 'id' columns) ---
            $table->foreignId('product_return_id')->constrained('product_returns')->onDelete('cascade'); 
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict'); 
            $table->foreignId('sale_item_id')->constrained('sale_items')->onDelete('restrict'); 

            // --- Item Data ---
            $table->unsignedInteger('quantity_returned');
            $table->decimal('refunded_price_per_unit', 10, 2);
            $table->decimal('total_line_refund', 10, 2);

            // --- Inventory Control Flag ---
            $table->boolean('inventory_adjusted')->default(false)->comment('1=Stock added back, 0=Stock marked as loss.');

            $table->timestamps();

            $table->unique(['product_return_id', 'sale_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_items');
    }
};
