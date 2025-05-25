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
        Schema::table('purchase_pools', function (Blueprint $table) {
            //
            $table->dropIndex(['end_date']);
            $table->dropIndex(['start_date']);
            $table->dropColumn('start_date', 'end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_pools', function (Blueprint $table) {
            //
            $table->date('start_date')->index();
            $table->date('end_date')->index();
        });
    }
};
