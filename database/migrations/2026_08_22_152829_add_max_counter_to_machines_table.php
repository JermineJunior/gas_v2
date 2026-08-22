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
        //لدعم حالات قلب العداد
        Schema::table('machines', function (Blueprint $table) {
            $table->unsignedBigInteger('max_counter')->default(9999999)->after('stock_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            $table->dropColumn('max_counter');
        });
    }
};
