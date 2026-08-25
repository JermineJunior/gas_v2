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
        Schema::table('deposits', function (Blueprint $table) {
            // مصروفات التواريخ التي شملتها هذه التسوية — ضروري لاحتساب
            // القيمة المرحَّلة (القديم الخام) عند تعديل التوريد لاحقاً
            $table->decimal('expenses_total', 15, 2)->default(0)->after('remaining');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->dropColumn('expenses_total');
        });
    }
};
