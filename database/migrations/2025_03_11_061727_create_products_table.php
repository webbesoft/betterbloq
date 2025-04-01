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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();

            $table->string('name', 100);
            $table->string('phone', 20);

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->default('Default Name');
            $table->string('image', 255)->default('Default Image');
            $table->text('description');
            $table->decimal('price', 10, 2)->default(0.00);
            $table->string('unit', 10)->default('pc');

            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');

            $table->unsignedBigInteger('vendor_id');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');

            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('purchase_pools', function (Blueprint $table) {
            $table->id();
            $table->date('start_date');
            $table->date('end_date');
            $table->date('target_delivery_date');
            $table->decimal('discount_percentage', 3, 2)->default(0.00);
            $table->integer('min_orders_for_discount')->default(0);
            $table->integer('max_orders')->default(0)->nullable();
            $table->string('status')->default('active');

            $table->unsignedBigInteger('vendor_id');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');

            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->string('name', 100);
            $table->string('phone', 20);
            $table->string('email', 100);
            $table->text('address');
            $table->string('status', 20)->default('pending');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('purchase_pool_id');
            $table->foreign('purchase_pool_id')->references('id')->on('purchase_pools')->onDelete('cascade');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('products');
        Schema::dropIfExists('purchase_pools');
        Schema::dropIfExists('orders');
    }
};
