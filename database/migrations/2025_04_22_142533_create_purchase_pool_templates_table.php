<?php

use App\Models\Product;
use App\Models\Vendor;
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
        Schema::create('purchase_pool_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Name for the template itself
            $table->text('description')->nullable(); // Optional description for the template
            $table->date('target_delivery_date')->nullable(); // Allow nullable if not always set in template
            $table->integer('min_orders_for_discount')->default(0);
            $table->integer('max_orders')->nullable()->default(null); // Use nullable if 0 means unlimited
            $table->string('status')->default('pending');
            $table->decimal('target_volume', 15, 2)->default(0); // Use decimal for volumes if needed
            $table->foreignIdFor(Vendor::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Product::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_pool_templates');
    }
};
