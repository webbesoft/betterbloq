<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds payment_method_id to orders so that off-session settlement
     * (MIT) can be performed at cycle finalisation without relying on
     * a pre-auth that expires after 7 days.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Stripe PaymentMethod ID (pm_xxx) captured at checkout via
            // setup_future_usage: 'off_session'. Used at cycle close to
            // create a fresh off-session PaymentIntent for the discounted amount.
            $table->string('payment_method_id')->nullable()->after('payment_intent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('payment_method_id');
        });
    }
};
