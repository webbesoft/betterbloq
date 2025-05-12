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
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Default Warehouse');
            $table->string('phone')->nullable();
            $table->decimal('total_capacity');
            $table->decimal('available_capacity');
            $table->string('total_capacity_unit');
            $table->decimal('default_storage_price_per_unit');
            $table->enum('default_storage_price_period', ['hours', 'days', 'weeks', 'months', 'quarters', 'years']);
            $table->json('supported_storage_conditions');
            $table->boolean('is_active');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
