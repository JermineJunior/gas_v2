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
        Schema::create('machine_details', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('station_id')->constrained()->cascadeOnDelete();
            $table->foreignId('machine_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('start_counter')->nullable();
            $table->bigInteger('end_counter')->nullable();
            $table->bigInteger('net')->nullable();
            $table->decimal('price',15,2)->nullable();
            $table->decimal('total',15,2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_details');
    }
};
