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
        Schema::create('storage_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('order_id');
            $table->date('requested_storage_start_date');
            $table->string('requested_storage_duration_estimate');
            $table->decimal('preliminary_storage_cost_estimate');
            $table->date('actual_storage_start_date')->nullable();
            $table->date('actual_storage_end_date')->nullable();
            $table->decimal('manually_entered_total_space_units')->nullable();
            $table->string('calculated_space_unit_type')->nullable();
            $table->unsignedBigInteger('applied_storage_tier_id')->nullable();
            $table->decimal('actual_rate_per_unit_per_period')->nullable();
            $table->string('billing_period_for_actuals')->nullable();
            $table->date('next_billing_date')->nullable();
            $table->decimal('total_actual_storage_cost_to_date')->default(0.0);
            $table->enum('status', ['pending_arrival', 'stored', 'partially_retrieved', 'retrieved', 'cancelled'])->default('pending_arrival');
            $table->string('notes')->nullable();

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            $table->foreign('applied_storage_tier_id')->references('id')->on('storage_tiers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_orders');
    }
};
