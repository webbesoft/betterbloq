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
        Schema::table('plan_limits', function (Blueprint $table) {
            //
            $table->string('key')->after('id')->default('default_key')->nullable();
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->string('billing_period')->default('monthly')->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plan_limits', function (Blueprint $table) {
            //
            $table->dropIfExists('key');
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->dropIfExists('billing_period');
        });
    }
};
