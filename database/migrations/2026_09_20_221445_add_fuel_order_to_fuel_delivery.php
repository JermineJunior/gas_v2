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
        Schema::table('fuel_delivieries', function (Blueprint $table) {
            // nullable — existing rows predate order-linking; required going forward via form validation
            $table->foreignId('fuel_order_id')->nullable()->after('tuncker_id')
                ->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fuel_delivieries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('fuel_order_id');
        });
    }
};
