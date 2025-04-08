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
        Schema::table('projects', function (Blueprint $table) {
            //
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
        Schema::table('purchase_pools', function (Blueprint $table) {
            $table->string('name')->default('Default Pool')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            //
            $table->dropForeign('projects_user_id_foreign');
            $table->dropColumn('user_id');
        });
        Schema::table('purchase_pools', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
