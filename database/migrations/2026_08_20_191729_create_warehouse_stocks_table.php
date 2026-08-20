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
        Schema::create('warehouse_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('fuel_type'); // same convention as operations.fuel_type / tunckers.fuel_type
            $table->decimal('current_stock', 15, 2)->default(0);
            $table->timestamps();

            // one balance row per warehouse+fuel_type combination
            $table->unique(['warehouse_id', 'fuel_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_stocks');
    }
};
