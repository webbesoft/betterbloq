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
            $table->float('applied_discount_percentage')->nullable()->after('total_price');
            $table->float('final_line_price')->nullable()->after('applied_discount_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_line_items', function (Blueprint $table) {
            //
            $table->dropColumn('applied_discount_percentage', 'final_line_price');
        });
    }
};
