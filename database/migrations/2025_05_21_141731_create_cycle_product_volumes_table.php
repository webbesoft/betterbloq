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
        Schema::create('cycle_product_volumes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_cycle_id');
            $table->index('purchase_cycle_id');
            $table->foreign('purchase_cycle_id')
                ->references('id')
                ->on('purchase_cycles')
                ->onDelete('cascade');
            $table->unsignedBigInteger('product_id');
            $table->index('product_id');
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade');
            $table->integer('total_aggregated_quantity')->default(0);
            $table->integer('achieved_discount_percentage')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cycle_product_volumes');
    }
};
