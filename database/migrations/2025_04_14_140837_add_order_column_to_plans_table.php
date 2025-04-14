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
        Schema::table('plans', function (Blueprint $table) {
            //
            $table->integer('order')->default(0)->after('stripe_plan');
            $table->boolean('is_popular')->default(false)->after('order');
            $table->enum('interval', ['monthly', 'yearly'])->default('monthly')->after('is_popular');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            //
            $table->dropIfExists(['order', 'is_popular', 'interval']);
        });
    }
};
