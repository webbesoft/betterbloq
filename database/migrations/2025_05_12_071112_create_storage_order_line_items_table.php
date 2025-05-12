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
        Schema::create('storage_order_line_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('storage_order_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity_stored');
            $table->decimal('manual_dimensions_length');
            $table->decimal('manual_dimensions_width');
            $table->decimal('manual_dimensions_height');
            $table->decimal('calculated_space_for_item');
            $table->date('entry_date')->nullable();
            $table->date('retrieval_date')->nullable();

            $table->timestamps();

            $table->foreign('storage_order_id')->references('id')->on('storage_orders')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_order_line_items');
    }
};
