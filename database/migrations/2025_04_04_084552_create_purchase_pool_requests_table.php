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
        Schema::create('purchase_pool_requests', function (Blueprint $table) {
            $table->id();

            $table->date('target_date');
            $table->integer('quantity')->default(1); // Added a quantity field
            $table->enum('status', ['pending', 'approved', 'rejected', 'pool_created'])->default('pending'); // Added a status field
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_pool_requests');
    }
};
