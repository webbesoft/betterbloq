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
        Schema::table('order_line_items', function (Blueprint $table) {
            //
            $table->float('price_per_unit')->default(0.0)->change();
            $table->float('total_price')->default(0.0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_line_items', function (Blueprint $table) {
            //
            $table->integer('price_per_unit');
        });
    }
};
