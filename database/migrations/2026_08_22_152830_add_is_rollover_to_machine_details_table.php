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
        Schema::table('machine_details', function (Blueprint $table) {
            $table->boolean('is_rollover')->default(false)->after('net');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machine_details', function (Blueprint $table) {
            $table->dropColumn('is_rollover');
        });
    }
};
