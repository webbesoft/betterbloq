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
        Schema::table('purchase_pools', function (Blueprint $table) {
            //
            $table->index('vendor_id');
            $table->index('product_id');

            $table->index('status');
            $table->index('start_date');
            $table->index('end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_pools', function (Blueprint $table) {
            //
            $table->dropIndex(['end_date']);      // purchase_pools_end_date_index
            $table->dropIndex(['start_date']);    // purchase_pools_start_date_index
            $table->dropIndex(['status']);        // purchase_pools_status_index
            $table->dropIndex(['product_id']);    // purchase_pools_product_id_index
            $table->dropIndex(['vendor_id']);     // purchase_pools_vendor_id_index
        });
    }
};
