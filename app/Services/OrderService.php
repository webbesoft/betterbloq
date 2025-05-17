<?php

namespace App\Services;

use App\Models\Product;
use App\Models\PurchasePool;
use App\Models\StorageOrder;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class OrderService
{
    public static function createOrder(User $user, array $data)
    {
        $itemEntries = data_get($data, 'items', []);
        if (empty($itemEntries)) {
            Session::flash('message', ['error' => 'No items provided in the order.']);

            return ['error' => 'No items provided in the order.', 'redirect_back' => true];
        }

        $productPreparationResult = self::prepareProductData($itemEntries);
        if (! $productPreparationResult['success']) {
            if ($productPreparationResult['flash'] ?? false) {
                Session::flash('message', ['error' => $productPreparationResult['error']]);
            }

            return ['error' => $productPreparationResult['error'], 'redirect_back' => ($productPreparationResult['flash'] ?? false)];
        }
        $productsById = $productPreparationResult['products'];

        $poolAssociationResult = self::findAndAssociatePurchasePools($productsById, $itemEntries);
        if (! $poolAssociationResult['success']) {
            Session::flash('message', ['error' => $poolAssociationResult['error']]);

            return ['error' => $poolAssociationResult['error'], 'redirect_back' => true];
        }
        /*
            $productPoolsMap structure:
            [product_id => ['pool' => PurchasePoolModel, 'quantity' => X, 'product' => ProductModel]]
        */
        $productPoolsMap = $poolAssociationResult['productPoolsMap'];

        $userExpectedDeliveryDateString = data_get($data, 'expected_delivery_date');
        if (empty($userExpectedDeliveryDateString)) {
            Session::flash('message', ['error' => 'Please provide your preferred delivery date.']);

            return ['redirect_back' => true, 'error' => 'Missing expected delivery date.'];
        }

        // DB::transaction(function () use (
        //     $user,
        //     $userExpectedDeliveryDateString,
        //     $productPoolsMap,
        //     $itemEntries) {
        $storageHandlingResult = self::handleStorageRequirements(
            $user,

            $userExpectedDeliveryDateString,
            $productPoolsMap
        );
        if (! $storageHandlingResult['success']) {
            Session::flash('message', ['error' => $storageHandlingResult['error']]);

            return ['error' => $storageHandlingResult['error'], 'redirect_back' => $storageHandlingResult['redirect_back'] ?? true];
        }
        $storageNeeded = $storageHandlingResult['storageNeeded'];
        $storageOrderId = $storageHandlingResult['storageOrderId'];
        $userExpectedDeliveryDate = $storageHandlingResult['userExpectedDeliveryDate'];

        $lineItemsResult = self::buildStripeLineItems($productPoolsMap);
        if (! $lineItemsResult['success']) {
            Session::flash('message', ['error' => $lineItemsResult['error']]);

            return ['error' => $lineItemsResult['error'], 'redirect_back' => true];
        }
        $line_items = data_get($lineItemsResult, 'line_items');

        $checkout_session_payload = self::prepareStripeCheckoutSessionPayload(
            $user,
            $productPoolsMap,
            $userExpectedDeliveryDate,
            $storageOrderId,
            $storageNeeded
        );

        try {
            $session = $user->checkout(
                $line_items,
                $checkout_session_payload
            );

            if (empty(data_get($session->toArray(), 'url'))) {
                throw new Exception('Missing session checkout URL.');
            }

            return [
                'success' => true,
                'url' => data_get($session->toArray(), 'url'),
            ];
        } catch (\Exception $e) {
            (new LogService)->logException($e, __CLASS__, __METHOD__, [
                'metadata' => [
                    'item_entries' => $itemEntries,
                    'expected_delivery_date' => $userExpectedDeliveryDateString,
                    'user_id' => $user->id,
                    'storage_order_id' => $storageOrderId,
                ],
            ]);
            Session::flash('message', ['error' => 'Could not initiate checkout. Please try again or contact support.']);

            // Changed from `return back();` to be consistent with other error returns
            return ['error' => 'Stripe checkout session creation failed.', 'redirect_back' => true];
        }
        // });
    }

    private static function prepareProductData(array $itemEntries): array
    {
        if (empty($itemEntries)) {
            return ['success' => false, 'error' => 'No items found in the request.', 'flash' => true];
        }

        $productIds = array_map(function ($item) {
            return $item['product_id'];
        }, $itemEntries);

        $products = Product::whereIn('id', $productIds)->with(['vendor'])->get()->keyBy('id');

        if (count($products) !== count(array_unique($productIds))) {
            $missingIds = array_diff($productIds, $products->keys()->all());
            Log::warning('Some products not found during order creation.', ['requested_ids' => $productIds, 'found_ids' => $products->keys()->all(), 'missing_ids' => $missingIds]);

            return ['success' => false, 'error' => 'One or more products in your order do not exist. Please review your cart.', 'flash' => true];
        }

        foreach ($products as $product) {
            if (empty($product->stripe_price_id)) {
                Log::error('Product missing stripe_price_id', ['product_id' => $product->id]);

                return ['success' => false, 'error' => "Product '{$product->name}' is not configured for sale. Cannot proceed.", 'flash' => true];
            }
        }

        return ['success' => true, 'products' => $products];
    }

    private static function findAndAssociatePurchasePools(Collection $products, array $itemEntries): array
    {
        $productPoolsMap = [];
        $errors = [];

        foreach ($itemEntries as $item) {
            $productId = $item['product_id'];
            $quantity = $item['quantity'];
            $product = $products->get($productId);

            if (! $product) {
                $errors[] = "Product with ID {$productId} not found.";

                continue;
            }

            $open_pool = PurchasePool::where('product_id', $productId)
                ->where('status', 'active')
                ->first();

            if (! $open_pool) {
                $errors[] = "No active purchase pool for product '{$product->name}'.";

                continue;
            }
            $productPoolsMap[$productId] = [
                'pool' => $open_pool,
                'quantity' => $quantity,
                'product' => $product,
            ];
        }

        if (! empty($errors)) {
            $errorMessage = 'Could not find active purchase pools for all products: '.implode('; ', $errors);
            Log::warning('Purchase pool association failed for some products.', ['errors' => $errors, 'item_entries' => $itemEntries]);

            return ['success' => false, 'error' => $errorMessage];
        }

        if (empty($productPoolsMap)) {
            return ['success' => false, 'error' => 'Failed to associate any products with purchase pools.'];
        }

        return ['success' => true, 'productPoolsMap' => $productPoolsMap];
    }

    private static function handleStorageRequirements(User $user, string $userExpectedDeliveryDateString, array $productPoolsMap): array
    {
        try {
            $userExpectedDeliveryDate = Carbon::parse($userExpectedDeliveryDateString);
        } catch (\Exception $e) {
            Log::info('Invalid user expected delivery date format.', ['date_string' => $userExpectedDeliveryDateString, 'user_id' => $user->id]);

            return ['success' => false, 'error' => 'Invalid delivery date format provided.', 'redirect_back' => true];
        }

        $storageNeeded = false;
        $storageOrderId = null;
        $earliestPoolTargetDeliveryDateForStorage = null;

        foreach ($productPoolsMap as $productId => $data) {
            $poolTargetDeliveryDate = Carbon::parse($data['pool']->target_delivery_date);
            if ($userExpectedDeliveryDate->isAfter($poolTargetDeliveryDate->copy()->addDays(3))) {
                $storageNeeded = true;
                if (is_null($earliestPoolTargetDeliveryDateForStorage) || $poolTargetDeliveryDate->lt($earliestPoolTargetDeliveryDateForStorage)) {
                    $earliestPoolTargetDeliveryDateForStorage = $poolTargetDeliveryDate;
                }
            }
        }

        if ($storageNeeded) {
            $defaultWarehouse = Warehouse::where('is_active', true)->orderBy('id')->first();
            if (! $defaultWarehouse) {
                Log::error('No active default warehouse found for creating storage order.', ['user_id' => $user->id]);

                return ['success' => false, 'error' => 'Storage configuration error. Please contact support.', 'redirect_back' => true];
            }

            try {
                $productNamesForStorage = implode(', ', array_map(fn ($item) => $item['product']->name, array_filter($productPoolsMap, function ($data) use ($userExpectedDeliveryDate) {
                    $poolTarget = Carbon::parse($data['pool']->target_delivery_date);

                    return $userExpectedDeliveryDate->isAfter($poolTarget->copy()->addDays(3));
                })));

                $storageOrder = StorageOrder::create([
                    'user_id' => $user->id,
                    'order_id' => null,
                    'warehouse_id' => $defaultWarehouse->id,
                    'requested_storage_start_date' => $earliestPoolTargetDeliveryDateForStorage ? $earliestPoolTargetDeliveryDateForStorage->copy()->addDay() : Carbon::now()->addDay(),
                    'requested_storage_duration_estimate' => $userExpectedDeliveryDate->diffForHumans($earliestPoolTargetDeliveryDateForStorage ? $earliestPoolTargetDeliveryDateForStorage->copy()->addDay() : Carbon::now()->addDay(), true).' (approx)',
                    'preliminary_storage_cost_estimate' => 0.00,
                    'status' => 'pending_arrival',
                    'notes' => 'Storage automatically initiated for products: '.$productNamesForStorage.' due to extended delivery date request.',
                    'manually_entered_total_space_units' => null,
                    'calculated_space_unit_type' => null,
                    'applied_storage_tier_id' => null,
                ]);
                $storageOrderId = $storageOrder->id;
                Log::info('Preliminary StorageOrder created.', ['id' => $storageOrderId, 'user_id' => $user->id, 'products_requiring_storage' => $productNamesForStorage]);
            } catch (\Exception $e) {
                (new LogService)->logException(
                    $e,
                    __CLASS__,
                    __METHOD__,
                    [
                        'user_id' => $user->id,
                        'productPoolsMap_keys' => array_keys($productPoolsMap),
                        'error' => $e->getMessage(),
                    ]);
                Log::error('Failed to create preliminary StorageOrder', [
                    'user_id' => $user->id,
                    'productPoolsMap_keys' => array_keys($productPoolsMap),
                    'error' => $e->getMessage(),
                ]);

                return ['success' => false, 'error' => 'Could not set up storage details. Please try again or contact support.', 'redirect_back' => true];
            }
        }

        return [
            'success' => true,
            'storageNeeded' => $storageNeeded,
            'storageOrderId' => $storageOrderId,
            'userExpectedDeliveryDate' => $userExpectedDeliveryDate,
        ];
    }

    private static function buildStripeLineItems(array $productPoolsMap): array
    {
        $line_items = [];
        foreach ($productPoolsMap as $productId => $data) {
            $product = data_get($data, 'product');
            $quantity = data_get($data, 'quantity');

            if (empty($product->stripe_price_id)) {
                Log::error('Critical: Product missing stripe_price_id at line item building stage.', ['product_id' => $product->id]);

                return ['success' => false, 'error' => "Configuration error for product '{$product->name}'. Cannot proceed."];
            }
            $line_items[] = [
                'price' => $product->stripe_price_id,
                'quantity' => $quantity,
            ];
        }
        if (empty($line_items)) {
            return ['success' => false, 'error' => 'No items could be prepared for checkout.'];
        }

        return ['success' => true, 'line_items' => $line_items];
    }

    private static function prepareStripeCheckoutSessionPayload(User $user, array $productPoolsMap, Carbon $userExpectedDeliveryDate, ?int $storageOrderId, bool $storageNeeded): array
    {
        $itemDetailsForMetadata = [];
        $totalQuantity = 0;
        $firstProductName = 'Multiple Items';
        $vendorIds = [];

        if (! empty($productPoolsMap)) {
            $firstProductData = reset($productPoolsMap);
            if ($firstProductData) {
                $firstProductName = data_get($firstProductData, 'product')->name.(count($productPoolsMap) > 1 ? ' and other materials' : '');
            }
        }

        foreach ($productPoolsMap as $productId => $data) {
            $itemDetailsForMetadata[] = [
                'product_id' => $productId,
                'product_name' => $data['product']->name,
                'quantity' => $data['quantity'],
                'purchase_pool_id' => $data['pool']->id,
                'vendor_id' => $data['product']->vendor_id,
            ];
            $totalQuantity += $data['quantity'];
            if ($data['product']->vendor_id) {
                $vendorIds[] = $data['product']->vendor_id;
            }
        }
        $vendorIds = array_unique($vendorIds);

        $checkout_session_payload = [
            'success_url' => route('orders.index').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('cart.show'),
            'metadata' => [
                'user_id' => $user->id,
                'expected_delivery_date' => $userExpectedDeliveryDate->toDateString(),
                'storage_order_id' => $storageOrderId,
                'storage_needed_flag' => $storageNeeded,
                'item_details' => json_encode($itemDetailsForMetadata),
                'vendor_id' => $data['product']->vendor_id,
                'total_items_quantity' => $totalQuantity,
            ],
            'payment_intent_data' => [
                'capture_method' => 'manual',
                'description' => "Order for {$firstProductName} - User: {$user->id}",
                'metadata' => [
                    'order_type' => 'purchase_pool_join_multiple',
                    'user_id' => $user->id,
                    'storage_order_id' => $storageOrderId,
                    'item_details_summary_hash' => md5(json_encode($itemDetailsForMetadata)),
                ],
            ],
            // 'mode' => 'payment',
        ];

        if ($user->stripe_id) {
            $checkout_session_payload['customer'] = $user->stripe_id;
        }

        return $checkout_session_payload;
    }
}
