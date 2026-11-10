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
        Schema::table('orders', function (Blueprint $table) {
            //
            $table->dropForeign(['purchase_pool_id']);
            $table->dropForeign(['product_id']);
            $table->dropIndex('orders_user_product_pool_index');
            $table->dropIndex(['purchase_pool_id']);
            $table->dropIndex(['product_id']);
            $table->dropColumn(['purchase_pool_id', 'product_id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('purchase_pool_id');
            $table->unsignedBigInteger('product_id');
            $table->foreign('purchase_pool_id')->references('id')->on('purchase_pools')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->index('purchase_pool_id');
            $table->index('product_id');
            $table->index(['user_id', 'product_id', 'purchase_pool_id'], 'orders_user_product_pool_index');
        });
    }
};
