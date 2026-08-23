<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stations', function (Blueprint $table) {
            $table->decimal('default_max_counter', 15, 2)->nullable()->after('order');
        });

        Schema::table('machines', function (Blueprint $table) {
            $table->boolean('use_rollover')->default(true)->after('max_counter');
        });
    }

    public function down(): void
    {
        Schema::table('stations', function (Blueprint $table) {
            $table->dropColumn('default_max_counter');
        });

        Schema::table('machines', function (Blueprint $table) {
            $table->dropColumn('use_rollover');
        });
    }
};
