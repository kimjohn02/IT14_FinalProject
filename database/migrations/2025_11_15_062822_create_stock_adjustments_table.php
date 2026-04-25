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
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();

            $table->dateTime('adjustment_date');

            $table->enum('adjustment_type', [
                'Physical Count',
                'Damage/Scrap',
                'Internal Use',
                'Error Correction',
                'Found Stock'
            ]);

            $table->text('reason_notes'); 

            $table->foreignId('processed_by_user_id')->constrained('users')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
