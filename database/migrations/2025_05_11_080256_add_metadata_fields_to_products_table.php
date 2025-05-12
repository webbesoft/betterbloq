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
        Schema::table('products', function (Blueprint $table) {
            //
            $table->boolean('storable')->default(true);
            $table->string('storage_unit_of_measure')->default('sq_ft_floor');
            $table->decimal('default_length')->nullable();
            $table->decimal('default_width')->nullable();
            $table->decimal('default_height')->nullable();
            $table->boolean('is_stackable')->default(false);
            $table->integer('max_stack_height_units')->nullable();
            $table->string('storage_conditions_required');
            $table->string('storage_handling_notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            //
            $table->dropColumn(['storable', 'storage_unit_of_measure', 'default_length', 'default_width', 'default_height', 'is_stackable', 'max_stack_height_units', 'storage_conditions_required', 'storage_handling_notes']);
        });
    }
};
