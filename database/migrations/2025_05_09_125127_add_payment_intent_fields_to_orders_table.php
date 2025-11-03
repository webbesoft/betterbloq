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
            $table->enum('status', ['created', 'payment_authorized', 'pending_finalization', 'processing_capture', 'completed', 'capture_failed', 'cancelled'])->default('created')->change();
            $table->string('payment_intent_id')->nullable()->after('stripe_session_id');

            $table->decimal('initial_amount', 10, 2)->after('total_amount');

            $table->decimal('final_amount', 10, 2)->nullable()->after('initial_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
            $table->dropColumn('payment_intent_id');
            $table->dropColumn('initial_amount');
            $table->dropColumn('final_amount');
            $table->string('status')->default('pending')->change();
        });
    }
};
