<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // A string accommodates paid/refund states on every supported database.
            $table->string('status', 32)->default('created')->change();
            $table->text('notes')->nullable();
        });
        Schema::table('order_line_items', function (Blueprint $table) {
            $table->unsignedBigInteger('original_unit_amount')->nullable();
            $table->unsignedBigInteger('charged_amount')->nullable();
            $table->decimal('final_line_price', 12, 2)->nullable()->change();
            $table->decimal('applied_discount_percentage', 5, 2)->nullable()->change();
            $table->decimal('total_price', 12, 2)->default(0)->change();
            $table->decimal('price_per_unit', 12, 2)->default(0)->change();
        });
        Schema::table('purchase_cycles', function (Blueprint $table) {
            $table->uuid('finalization_token')->nullable();
            $table->timestamp('finalization_lease_until')->nullable();
        });
        Schema::create('checkout_attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('purchase_cycle_id')->constrained();
            $table->string('fingerprint', 64);
            $table->string('status')->default('creating');
            $table->string('stripe_session_id')->nullable()->unique();
            $table->json('snapshot');
            $table->json('stripe_payload');
            $table->text('url')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();
            $table->index(['user_id', 'fingerprint']);
            $table->index(['purchase_cycle_id', 'status']);
        });
        Schema::create('payment_operations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('order_id')->unique()->constrained();
            $table->unsignedBigInteger('target_amount');
            $table->json('line_amounts');
            $table->string('kind')->nullable();
            $table->json('request')->nullable();
            $table->json('response')->nullable();
            $table->string('status')->default('pending');
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
        Schema::create('payment_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
        // Collapse duplicate keys, then rebuild quantities from committed paid
        // line items. This also restores missing aggregates in one grouped scan.
        $groups = DB::table('cycle_product_volumes')->select('purchase_cycle_id', 'product_id')
            ->groupBy('purchase_cycle_id', 'product_id')->cursor();
        foreach ($groups as $group) {
            $ids = DB::table('cycle_product_volumes')->where('purchase_cycle_id', $group->purchase_cycle_id)
                ->where('product_id', $group->product_id)->orderBy('id')->pluck('id');
            DB::table('cycle_product_volumes')->where('id', $ids->first())->update(['total_aggregated_quantity' => 0]);
            DB::table('cycle_product_volumes')->whereIn('id', $ids->slice(1))->delete();
        }
        $volumes = DB::table('order_line_items')
            ->join('orders', 'orders.id', '=', 'order_line_items.order_id')
            ->join('purchase_pools', 'purchase_pools.id', '=', 'order_line_items.purchase_pool_id')
            ->whereNotNull('order_line_items.product_id')->whereNotNull('purchase_pools.purchase_cycle_id')
            ->whereNotIn('orders.status', ['cancelled', 'created'])
            ->select('purchase_pools.purchase_cycle_id', 'order_line_items.product_id')
            ->selectRaw('SUM(order_line_items.quantity) as quantity')
            ->groupBy('purchase_pools.purchase_cycle_id', 'order_line_items.product_id')->cursor();
        foreach ($volumes as $volume) {
            DB::table('cycle_product_volumes')->updateOrInsert([
                'purchase_cycle_id' => $volume->purchase_cycle_id, 'product_id' => $volume->product_id,
            ], ['total_aggregated_quantity' => $volume->quantity, 'updated_at' => now(), 'created_at' => now()]);
        }
        Schema::table('cycle_product_volumes', function (Blueprint $table) {
            $table->unique(['purchase_cycle_id', 'product_id'], 'cycle_product_unique');
            $table->decimal('achieved_discount_percentage', 5, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        // Financial operation history must survive application rollbacks.
        throw new RuntimeException('This migration requires an explicit data-preserving rollback plan.');
    }
};
