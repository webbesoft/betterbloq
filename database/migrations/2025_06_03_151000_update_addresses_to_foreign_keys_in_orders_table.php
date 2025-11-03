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
            $table->unsignedBigInteger('shipping_address_id')->nullable()->after('user_id');
            $table->unsignedBigInteger('billing_address_id')->nullable()->after('shipping_address_id');
            $table->foreign('shipping_address_id')->references('id')->on('addresses')->onDelete('set null');
            $table->foreign('billing_address_id')->references('id')->on('addresses')->onDelete('set null');
            $table->dropColumn('shipping_address');
            $table->dropColumn('billing_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
            $table->dropForeign(['shipping_address_id']);
            $table->dropForeign(['billing_address_id']);
            $table->dropColumn('shipping_address_id');
            $table->dropColumn('billing_address_id');
            $table->string('shipping_address')->nullable()->after('user_id');
            $table->string('billing_address')->nullable()->after('shipping_address');
        });
    }
};
