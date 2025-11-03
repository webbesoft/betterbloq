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
            $table->unsignedBigInteger('purchase_cycle_id')->nullable()->after('id');
            $table->index('purchase_cycle_id');
            $table->foreign('purchase_cycle_id')
                ->references('id')
                ->on('purchase_cycles')
                ->onDelete('cascade');
            $table->removeColumn('quantity');
            $table->removeColumn('total_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
            $table->dropForeign(['purchase_cycle_id']);
            $table->dropIndex(['purchase_cycle_id']);
            $table->dropColumn('purchase_cycle_id');
            $table->integer('quantity')->after('product_id');
            $table->decimal('total_amount', 10, 2)->after('quantity');
        });
    }
};
