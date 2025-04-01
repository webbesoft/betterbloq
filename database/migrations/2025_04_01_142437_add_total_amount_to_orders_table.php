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
            $table->decimal('total_amount', 10, 2);

            $table->unsignedBigInteger('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');

            $table->unsignedBigInteger('vendor_id');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
        });

        Schema::table('purchase_pools', function (Blueprint $table) {
            $table->decimal('target_amount', 10, 2);
            $table->decimal('current_amount', 10, 2);
        });

        Schema::create('purchase_pool_watchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('purchase_pool_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Ensure that a user can only watch a purchase pool once
            $table->unique(['user_id', 'purchase_pool_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
            $table->dropColumn('total_amount');
            $table->dropForeign('project_id');
        });

        Schema::table('purchase_pools', function (Blueprint $table) {
            $table->dropColumn('target_amount');
            $table->dropColumn('current_amount');
        });

        Schema::dropIfExists('purchase_pool_watchers');
    }
};
