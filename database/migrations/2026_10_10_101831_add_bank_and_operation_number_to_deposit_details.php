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
        Schema::table('deposit_details', function (Blueprint $table) {
              $table->string('bank_name')->nullable()->after('deposit_desc');
              $table->string('operation_number')->nullable()->after('bank_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deposit_details', function (Blueprint $table) {
            $table->dropColumn('bank_name');
            $table->dropColumn('operation_number');
        });
    }
};
