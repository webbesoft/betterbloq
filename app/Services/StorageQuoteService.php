<?php

namespace App\Services;

use App\Models\Product;
use App\Models\PurchasePool;
use App\Models\Warehouse;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

class StorageQuoteService
{
    public function quote(Product $product, PurchasePool $pool, array $item): ?array
    {
        $delivery = CarbonImmutable::parse($item['expected_delivery_date'])->startOfDay();
        $arrival = CarbonImmutable::parse($pool->target_delivery_date)->startOfDay();
        if ($delivery->isBefore($arrival)) {
            throw ValidationException::withMessages(['items' => 'Delivery cannot precede the purchase pool delivery date.']);
        }
        $days = (int) $arrival->diffInDays($delivery, false) - 7;
        if ($days <= 0) {
            return null;
        }
        if (! $product->storable || ! ($item['requires_storage_acknowledged'] ?? false)) {
            throw ValidationException::withMessages(['items' => 'Extended delivery requires storage acknowledgement and a storable product.']);
        }
        $warehouse = Warehouse::whereKey($product->preferred_warehouse_id)->where('is_active', true)->with('storageTiers')->first();
        if (! $warehouse) {
            throw ValidationException::withMessages(['items' => 'No active storage provider is configured for this product.']);
        }
        // Match the storefront's existing feet-based space and stacking calculation.
        $factor = match ($product->storage_unit_of_measure) {
            'cm' => 1 / 30.48, 'm' => 1 / 0.3048, 'in' => 1 / 12,
            'ft', 'sq_ft_floor' => 1.0,
            default => throw ValidationException::withMessages(['items' => 'Unsupported storage dimension unit.']),
        };
        $length = (float) $product->default_length * $factor;
        $width = (float) $product->default_width * $factor;
        $height = (float) $product->default_height * $factor;
        $quantity = (int) $item['quantity'];
        if ($length <= 0 || $width <= 0) {
            throw ValidationException::withMessages(['items' => 'Storage dimensions are not configured.']);
        }
        if ($warehouse->total_capacity_unit === 'sq ft') {
            $stack = 1;
            if ($product->is_stackable) {
                $stack = max(1, (int) $product->max_stack_height_units);
                if ($warehouse->max_height > 0 && $height > 0) {
                    $stack = max(1, min($stack, (int) floor($warehouse->max_height / $height)));
                }
            }
            $space = $length * $width * ceil($quantity / $stack);
        } elseif ($warehouse->total_capacity_unit === 'cu ft' && $height > 0) {
            $space = $length * $width * $height * $quantity;
        } else {
            throw ValidationException::withMessages(['items' => 'Unsupported storage capacity unit or missing height.']);
        }
        $tier = $warehouse->storageTiers->sortByDesc('min_space_units')->first(fn ($tier) => $space >= $tier->min_space_units && $space < $tier->max_space_units);
        $rate = $tier ? $tier->price_per_space_unit : $warehouse->default_storage_price_per_unit;
        $period = $tier ? $tier->billing_period : $warehouse->default_storage_price_period;
        $periodDays = match ($period) {
            'day', 'days' => 1, 'week', 'weeks' => 7, 'month', 'months' => 30,
            default => throw ValidationException::withMessages(['items' => 'Unsupported storage billing period.']),
        };
        if (! is_finite($rate) || $rate < 0) {
            throw ValidationException::withMessages(['items' => 'Invalid storage rate.']);
        }

        return [
            'warehouse_id' => $warehouse->id, 'days' => $days,
            'start_date' => $arrival->addDays(8)->toDateString(),
            'amount' => (int) round($space * (float) $rate * $days * 100 / $periodDays),
        ];
    }
}
