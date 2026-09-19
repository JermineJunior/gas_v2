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
        Schema::table('fuel_orders', function (Blueprint $table) {
             // nullable — existing rows predate this feature and won't have these values
            $table->tinyInteger('fuel_type')->nullable()->after('quantity');
            $table->decimal('price_per_liter', 15, 2)->nullable()->after('fuel_type');
            $table->decimal('total', 15, 2)->nullable()->after('price_per_liter'); // stored, computed as quantity * price_per_liter at save time
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fuel_orders', function (Blueprint $table) {
             $table->dropColumn(['fuel_type', 'price_per_liter', 'total']);
        });
    }
};
