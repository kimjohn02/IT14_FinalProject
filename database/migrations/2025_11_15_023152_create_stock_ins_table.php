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
            Schema::create('stock_ins', function (Blueprint $table) {
                $table->id();

                $table->dateTime('stock_in_date');
                $table->string('reference_no', 100)->nullable();
                
                $table->unsignedBigInteger('received_by_user_id');
                $table->foreign('received_by_user_id')->references('id')->on('users')->onDelete('restrict');
        
                $table->string('status')->default('completed');
                
                $table->timestamps();
            });
        }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_ins');
    }
};
