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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();

            $table->string('supplier_name', 150);
            $table->string('contactNO', 50)->nullable();
            $table->string('address', 255)->nullable();
            
            // Soft Delete / Audit Fields
            $table->boolean('is_active')->default(true);
            $table->dateTime('date_disabled')->nullable();
            $table->string('archive_reason', 255)->nullable(); 
            
            // FK to users table for audit trail
            $table->unsignedBigInteger('disabled_by_user_id')->nullable();
            $table->foreign('disabled_by_user_id')->references('id')->on('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
