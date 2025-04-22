<?php

use App\Models\PurchasePoolTemplate;
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
        Schema::create('purchase_pool_template_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(PurchasePoolTemplate::class)->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description');
            $table->decimal('discount_percentage', 5, 2);
            $table->decimal('min_volume', 15, 2)->nullable();
            $table->decimal('max_volume', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_pool_template_tiers');
    }
};
