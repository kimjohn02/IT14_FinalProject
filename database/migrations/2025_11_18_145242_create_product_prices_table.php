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
        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            
            // The current selling price that will be used by the POS/Sales system
            $table->decimal('retail_price', 10, 2); 
            
            // Optional, but recommended: Reference the Stock In transaction that initiated this price change.
            $table->foreignId('stock_in_id')->nullable()->constrained('stock_ins')->onDelete('set null');

            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->onDelete('set null');
                
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_prices');
    }
};
