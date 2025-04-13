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
            $table->renameColumn('current_amount', 'current_volume');
            $table->renameColumn('target_amount', 'target_volume');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_pools', function (Blueprint $table) {
            //
            $table->renameColumn('current_volume', 'current_volume');
            $table->renameColumn('target_volume', 'target_volume');
        });
    }
};
