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
        Schema::create('purchase_pool_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->decimal('min_volume')->nullable();
            $table->decimal('max_volume')->nullable();
            $table->float('discount_percentage');
            $table->unsignedBigInteger('purchase_pool_id');
            $table->foreign('purchase_pool_id')->references('id')->on('purchase_pools')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_pool_tiers');
    }
};
