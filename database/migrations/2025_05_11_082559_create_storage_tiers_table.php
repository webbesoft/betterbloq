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
        Schema::create('storage_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('warehouse_id');
            $table->decimal('min_space_units');
            $table->decimal('max_space_units');
            $table->decimal('price_per_space_unit');
            $table->string('billing_period');
            $table->integer('min_duration')->default(1);
            $table->string('duration_unit');
            $table->json('conditions'); // e.g. climate_controlled
            $table->string('notes')->nullable();

            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_tiers');
    }
};
