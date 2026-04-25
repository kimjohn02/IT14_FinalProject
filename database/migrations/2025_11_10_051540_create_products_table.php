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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('sku', 50)->unique();
            $table->string('name', 150);
            $table->string('description', 500)->nullable();
            $table->string('model', 100)->nullable();

            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('restrict');

            $table->string('image_path', 255)->nullable();
            
            // Barcode: Unique, but nullable
            $table->string('manufacturer_barcode', 30)->unique()->nullable();

            $table->unsignedBigInteger('default_supplier_id');
            $table->foreign('default_supplier_id')
                ->references('id')
                ->on('suppliers')
                ->onDelete('restrict');
                   
            // Inventory
            $table->integer('quantity_in_stock')->default(0);
            $table->integer('reorder_level');
            $table->decimal('latest_unit_cost', 10, 2)->nullable();

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
        Schema::dropIfExists('products');
    }
};
