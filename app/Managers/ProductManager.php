<?php

namespace App\Managers;

use App\Models\Log;
use App\Models\Product;
use App\Services\LogService;
use Illuminate\Database\Eloquent\Model;
use Stripe\Exception\ApiErrorException;

class ProductManager extends Manager
{
    public function onSaving(mixed $data): mixed
    {
        return $data;
    }

    public function onCreated(Model $product): void
    {
        if ($product instanceof Product) {
            try {
                $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
                $stripeProduct = $stripe->products->create([
                    'name' => $product->name.' from '.$product->vendor->name,
                    'description' => $product->description ?? null,
                ]);

                // Create Stripe Price
                $stripePrice = $stripe->prices->create([
                    'product' => $stripeProduct->id,
                    'unit_amount' => $product->price * 100,
                    'currency' => 'usd',
                ]);

                // Update the local Product record with Stripe IDs
                $product->update([
                    'stripe_product_id' => $stripeProduct->id,
                    'stripe_price_id' => $stripePrice->id,
                ]);
            } catch (ApiErrorException $e) {
                (new LogService)->createLog('error', $e->getMessage(), Product::class, 'boot::created');
                Log::error('Error creating Stripe product: '.$e->getMessage());
            }
        }
    }
}
