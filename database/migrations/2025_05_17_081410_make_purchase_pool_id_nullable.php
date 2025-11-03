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
            $table->dropForeign('orders_purchase_pool_id_foreign');
            $table->dropColumn('purchase_pool_id');
            $table->dropForeign('orders_product_id_foreign');
            $table->dropColumn('product_id');
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
        });
    }
};
