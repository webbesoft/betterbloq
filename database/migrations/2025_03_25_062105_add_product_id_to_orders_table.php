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
            $table->foreignId('product_id')->constrained();
            $table->text('billing_address')->nullable();
            $table->text('shipping_address')->nullable();
            $table->dropColumn(['name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
            $table->dropForeign(['product_id']);
            $table->dropColumn(['billing_address', 'shipping_address']);
        });
    }
};
