<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            // تفرد الاسم داخل المحطة الواحدة فقط، وليس على مستوى الجدول
            $table->dropUnique('stocks_name_unique');
            $table->unique(['name', 'station_id']);
        });
    }

    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropUnique(['name', 'station_id']);
            $table->unique('name');
        });
    }
};
