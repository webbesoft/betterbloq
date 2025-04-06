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
            $table->dropColumn('discount_percentage');
            $table->enum('status', ['pending', 'active', 'closed'])->default('pending')->change();
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->enum('status', ['planning', 'in-progress', 'complete'])->default('planning')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_pools', function (Blueprint $table) {
            //
            $table->decimal('discount_percentage')->after('status')->default(0.0);
            $table->string('status')->after('discount_percentage')->default('active')->change();
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->string('status')->default('ongoing')->change();
        });
    }
};
