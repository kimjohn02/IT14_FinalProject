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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('sale_id');
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
            
            $table->dateTime('payment_date')->useCurrent();
            $table->enum('payment_method', ['Cash', 'GCash', 'Card']);
            
            $table->decimal('amount_tendered', 10, 2);
            $table->decimal('change_given', 10, 2)->default(0); // Change if cash
            $table->string('reference_no', 100)->nullable(); // For Card/GCash

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
