<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // المحطات: إعدادات افتراضية للعدادات
        Schema::table('stations', function (Blueprint $table) {
            $table->decimal('default_allowed_rollover', 15, 2)->nullable()->after('default_max_counter');
            $table->boolean('default_use_rollover')->default(true)->after('default_allowed_rollover');
        });

        // الماكينات: حد تصفير مسموح لكل ماكينة
        Schema::table('machines', function (Blueprint $table) {
            $table->decimal('allowed_rollover', 15, 2)->nullable()->after('max_counter');
        });

        // قرادات العدادات: سير عمل الاعتماد
        Schema::table('machine_details', function (Blueprint $table) {
            $table->boolean('requires_approval')->default(false)->after('is_rollover');
            $table->string('approval_status')->nullable()->after('requires_approval'); // null/pending/approved/rejected
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('approval_status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('stations', function (Blueprint $table) {
            $table->dropColumn(['default_allowed_rollover', 'default_use_rollover']);
        });

        Schema::table('machines', function (Blueprint $table) {
            $table->dropColumn('allowed_rollover');
        });

        Schema::table('machine_details', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['requires_approval', 'approval_status', 'approved_at']);
        });
    }
};
