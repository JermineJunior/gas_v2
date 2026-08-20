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
        Schema::create('warehouse_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('fuel_type'); // same convention as operations.fuel_type

            // 0 = addition (purchase/incoming)
            // 1 = subtraction (withdrawal — fuel taken out)
            // 2 = transfer out (source warehouse)
            // 3 = transfer in (destination warehouse)
            $table->tinyInteger('type');

            $table->decimal('quantity', 15, 2);
            $table->decimal('balance_after', 15, 2)->default(0); // running balance for this warehouse+fuel_type after this row

            // set only when type = 1 (withdrawal)
            $table->foreignId('withdrawal_id')->nullable()
                ->constrained('warehouse_withdrawals')->cascadeOnDelete();

            // links a transfer_out row to its paired transfer_in row (and vice versa)
            $table->foreignId('related_transaction_id')->nullable()
                ->constrained('warehouse_transactions')->nullOnDelete();
            // driver/car logged directly here for additions (type=0), since additions
            // have no dedicated model of their own like withdrawals do. For withdrawals
            // (type=1), driver_name/car_number live on warehouse_withdrawals instead —
            // these columns stay null for that type.
            $table->string('driver_name')->nullable();
            $table->string('car_number')->nullable();
            $table->date('date');
            $table->string('source')->nullable(); // supplier name, for additions
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_transactions');
    }
};
