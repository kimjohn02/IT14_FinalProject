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
        Schema::create('product_returns', function (Blueprint $table) {
            $table->id();

            // --- Foreign Keys (Linking to Primary 'id' columns) ---
            $table->foreignId('sale_id')->constrained('sales')->onDelete('restrict'); 
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict'); 
            $table->foreignId('refund_payment_id')->nullable()->constrained('payments')->onDelete('restrict'); 

            // --- Financial Data ---
            $table->decimal('total_refund_amount', 10, 2);
            
            // --- Audit Data ---
            $table->enum('return_reason', [
                'Defective',
                'Wrong Item', 
                'Customer Change Mind',
                'Other'
            ]);
            
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->index('created_at');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};
