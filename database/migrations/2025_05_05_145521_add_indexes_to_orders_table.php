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
            $table->index('user_id');
            $table->index('purchase_pool_id');
            $table->index('product_id');
            $table->index('project_id');
            $table->index('vendor_id');

            $table->index('status');
            $table->index(['user_id', 'product_id', 'purchase_pool_id'], 'orders_user_product_pool_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
            $table->dropIndex('orders_user_product_pool_index');
            $table->dropIndex(['status']);

            $table->dropIndex(['vendor_id']);
            $table->dropIndex(['project_id']);
            $table->dropIndex(['product_id']);
            $table->dropIndex(['purchase_pool_id']);
            $table->dropIndex(['user_id']);
        });
    }
};
