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
            $table->unsignedBigInteger('purchase_cycle_id')->nullable()->after('id');
            $table->index('purchase_cycle_id');
            $table->foreign('purchase_cycle_id')
                ->references('id')
                ->on('purchase_cycles')
                ->onDelete('cascade');

            // Add new column
            $table->float('final_achieved_discount_percentage')->nullable()->after('status');

            $table->dropColumn('status');
            $table->enum('cycle_status', ['accumulating', 'finalized', 'failed'])->default('accumulating')->after('max_orders');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_pools', function (Blueprint $table) {
            //
            $table->dropForeign(['purchase_cycle_id']);
            $table->dropIndex(['purchase_cycle_id']);
            $table->dropColumn('purchase_cycle_id');
            $table->dropColumn('final_achieved_discount_percentage');
            $table->renameColumn('cycle_status', 'status');
            $table->enum('status', ['pending', 'active', 'closed'])->default('pending')->change();
        });
    }
};
