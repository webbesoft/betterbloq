<?php

namespace App\Services;

use App\Enums\OrderStatusEnum;
use App\Managers\PurchasePoolManager;
use App\Models\CycleProductVolume;
use App\Models\Order;
use App\Models\OrderLineItem;
use App\Models\Product;
use App\Models\PurchaseCycle;
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

        $storageHandlingResult = self::handleItemStorageRequirements($user, $itemEntries, $productPoolsMap);
        if (! $storageHandlingResult['success']) {
            Session::flash('message', ['error' => $storageHandlingResult['error']]);

            return ['error' => $storageHandlingResult['error'], 'redirect_back' => true];
        }
        $storageOrderIds = $storageHandlingResult['storageOrderIds'];

        $orderProductSubtotal = 0;
        $orderStorageCostApplied = 0;
        $orderFinalLinePrice = 0;

        foreach ($itemEntries as $cartItem) {
            $orderProductSubtotal += (float) data_get($cartItem, 'product_subtotal', 0);
            $orderStorageCostApplied += (float) data_get($cartItem, 'storage_cost_applied', 0);
            $orderFinalLinePrice += (float) data_get($cartItem, 'final_line_price', 0);
        }

        $lineItemsResult = self::buildStripeLineItems($productPoolsMap, $orderStorageCostApplied);
        if (! $lineItemsResult['success']) {
            Session::flash('message', ['error' => $lineItemsResult['error']]);

            return ['error' => $lineItemsResult['error'], 'redirect_back' => true];
        }
        $line_items = data_get($lineItemsResult, 'line_items');

        $prepareStripeSessionPayloadData = [
            'user' => $user,
            'productPoolsMap' => $productPoolsMap,
            'itemEntries' => $itemEntries,
            'storageOrderIds' => $storageOrderIds,
            'storageCostApplied' => $orderStorageCostApplied,
            'finalLinePrice' => $orderFinalLinePrice,
            'productSubtotal' => $orderProductSubtotal,
        ];

        $checkout_session_payload = self::prepareStripeCheckoutSessionPayload(
            $prepareStripeSessionPayloadData
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
                    'user_id' => $user->id,
                ],
            ]);
            Session::flash('message', ['error' => 'Could not initiate checkout. Please try again or contact support.']);

            return ['error' => 'Stripe checkout session creation failed.', 'redirect_back' => true];
        }
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
            $purchaseCycleId = $item['purchase_cycle_id'];
            $product = $products->get($productId);

            if (! $product) {
                $errors[] = "Product with ID {$productId} not found.";

                continue;
            }

            $open_pool = PurchasePool::where('product_id', $productId)
                ->where('purchase_cycle_id', $purchaseCycleId)
                ->where('cycle_status', PurchasePool::STATUS_ACCUMULATING)
                ->first();

            if (! $open_pool) {
                $errors[] = "No active purchase pool for product '{$product->name}'.";

                continue;
            }
            $productPoolsMap[$productId] = [
                'pool' => $open_pool,
                'quantity' => $quantity,
                'product' => $product,
                'expected_delivery_date' => $item['expected_delivery_date'],
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

    private static function handleItemStorageRequirements(User $user, array $itemEntries, array $productPoolsMap): array
    {
        $storageOrderIds = [];

        foreach ($itemEntries as $item) {
            if (! ($item['requires_storage_acknowledged'] ?? false)) {
                continue;
            }

            try {
                $defaultWarehouse = Warehouse::where('is_active', true)->orderBy('id')->first();
                if (! $defaultWarehouse) {
                    Log::error('No active default warehouse found for creating storage order.', ['user_id' => $user->id]);

                    return ['success' => false, 'error' => 'Storage configuration error. Please contact support.'];
                }

                $product = $productPoolsMap[$item['product_id']]['product'];
                $pool = $productPoolsMap[$item['product_id']]['pool'];

                $storageOrder = StorageOrder::create([
                    'user_id' => $user->id,
                    'order_id' => null,
                    'warehouse_id' => $defaultWarehouse->id,
                    'requested_storage_start_date' => Carbon::parse($pool->target_delivery_date)->addDay(),
                    'requested_storage_duration_estimate' => Carbon::parse($item['expected_delivery_date'])->diffForHumans(Carbon::parse($pool->target_delivery_date)->addDay(), true).' (approx)',
                    'preliminary_storage_cost_estimate' => (float) ($item['storage_cost_applied'] ?? 0),
                    'status' => 'pending_arrival',
                    'notes' => 'Storage for product: '.$product->name.' with extended delivery date.',
                ]);

                $storageOrderIds[] = $storageOrder->id;

            } catch (\Exception $e) {
                Log::error('Failed to create storage order for item', [
                    'user_id' => $user->id,
                    'product_id' => $item['product_id'],
                    'error' => $e->getMessage(),
                ]);

                return ['success' => false, 'error' => 'Could not set up storage details. Please try again or contact support.'];
            }
        }

        return [
            'success' => true,
            'storageOrderIds' => $storageOrderIds,
        ];
    }

    private static function buildStripeLineItems(array $productPoolsMap, float $storageCostApplied): array
    {
        $line_items = [];
        foreach ($productPoolsMap as $productId => $data) {
            $product = data_get($data, 'product');
            $quantity = data_get($data, 'quantity');
            $pool = data_get($data, 'pool');

            if (empty($product->stripe_price_id)) {
                Log::error('Critical: Product missing stripe_price_id at line item building stage.', ['product_id' => $product->id]);

                return ['success' => false, 'error' => "Configuration error for product '{$product->name}'. Cannot proceed."];
            }

            $appliedDiscountPercentage = PurchasePoolManager::getLivePurchaseTier($data['pool'], data_get($data, 'quantity'))?->discount_percentage;

            info('applied discount ', ['perc' => $appliedDiscountPercentage]);

            if ($appliedDiscountPercentage > 0) {
                // Use price_data with discounted amount
                $originalPrice = $product->price; // Assuming you have price stored on product
                $discountedPrice = $originalPrice * (1 - $appliedDiscountPercentage / 100);

                $line_items[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $product->name,
                        ],
                        'unit_amount' => (int) round($discountedPrice * 100),
                    ],
                    'quantity' => $quantity,
                ];
            } else {
                $line_items[] = [
                    'price' => $product->stripe_price_id,
                    'quantity' => $quantity,
                ];
            }
        }

        if ($storageCostApplied > 0) {
            $line_items[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'Storage Fee',
                        'description' => 'Fee for extended storage period.', // Optional
                    ],
                    'unit_amount' => (int) round($storageCostApplied * 100),
                ],
                'quantity' => 1,
            ];
        }

        if (empty($line_items)) {
            return ['success' => false, 'error' => 'No items could be prepared for checkout.'];
        }

        return ['success' => true, 'line_items' => $line_items];
    }

    private static function prepareStripeCheckoutSessionPayload(array $prepareSessionPayload): array
    {
        $productPoolsMap = data_get($prepareSessionPayload, 'productPoolsMap');
        $user = data_get($prepareSessionPayload, 'user');
        $storageOrderId = data_get($prepareSessionPayload, 'storageOrderId');
        $storageNeeded = data_get($prepareSessionPayload, 'storageNeeded');
        $storageCostApplied = data_get($prepareSessionPayload, 'storageCostApplied');
        $finalLinePrice = data_get($prepareSessionPayload, 'finalLinePrice');
        $productSubtotal = data_get($prepareSessionPayload, 'productSubtotal');

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
                'purchase_cycle_id' => $data['pool']->purchase_cycle_id,
                'vendor_id' => $data['product']->vendor_id,
                'expected_delivery_date' => data_get($data, 'expected_delivery_date'),
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
                'storage_order_id' => $storageOrderId,
                'storage_needed_flag' => $storageNeeded,
                'item_details' => json_encode($itemDetailsForMetadata),
                'vendor_id' => $data['product']->vendor_id,
                'product_id' => $data['product']->id,
                'total_items_quantity' => $totalQuantity,
                'product_subtotal' => (string) $productSubtotal,
                'storage_cost_applied' => (string) $storageCostApplied,
                'final_line_price_charged' => (string) $finalLinePrice,
            ],
            'payment_intent_data' => [
                'capture_method' => 'manual',
                'description' => "Order for {$firstProductName} - User: {$user->id} (Total: ".number_format((float) $finalLinePrice, 2).')',
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

    public function createOrderFromStripeSession(
        array $session,
        User $user,
        array $metadata,
        \Stripe\Collection $lineItemsFromStripe,
        array $itemDetailsLookup
    ): ?Order {
        return DB::transaction(function () use ($session, $user, $metadata, $lineItemsFromStripe, $itemDetailsLookup) {
            $mainOrder = $this->createMainOrder($session, $user, $metadata, $itemDetailsLookup);
            if (! $mainOrder) {
                return null;
            }

            $this->processOrderLineItems($mainOrder, $lineItemsFromStripe, $itemDetailsLookup);
            $this->processStorageOrder($mainOrder, $metadata, $session['id']);

            // TODO: Trigger events for notifications instead of direct calls
            // event(new OrderCreated($mainOrder));

            return $mainOrder;
        });
    }

    private function createMainOrder(array $session, User $user, array $metadata, array $itemDetailsLookup): ?Order
    {
        $vendorId = data_get($metadata, 'vendor_id');
        $productSubtotalMeta = data_get($metadata, 'product_subtotal');
        $storageCostAppliedMeta = data_get($metadata, 'storage_cost_applied');
        if (! $vendorId && ! empty($itemDetailsLookup)) {
            $firstItemDetail = reset($itemDetailsLookup);
            if ($firstItemDetail && isset($firstItemDetail['vendor_id'])) {
                // #! All *should* have the same vendor and purchase cycle
                $vendorId = $firstItemDetail['vendor_id'];
                $purchaseCycleId = data_get($firstItemDetail, 'purchase_cycle_id');
            }
        }

        if (! $vendorId) {
            Log::error('Webhook: Vendor ID could not be determined for order creation.', ['session_id' => $session['id'], 'metadata' => $metadata]);
        }

        $productSubtotalMeta = data_get($metadata, 'product_subtotal');
        $storageCostAppliedMeta = data_get($metadata, 'storage_cost_applied');

        return Order::create([
            'user_id' => $user->id,
            'purchase_cycle_id' => $purchaseCycleId,
            'email' => data_get($session, 'customer_details.email') ?? $user->email,
            'phone' => data_get($session, 'customer_details.phone') ?? '+1 (456) 7890',
            'address' => json_encode(data_get($session, 'customer_details.address') ?? null),
            'status' => OrderStatusEnum::PAYMENT_AUTHORIZED,
            'stripe_session_id' => data_get($session, 'id'),
            'payment_intent_id' => data_get($session, 'payment_intent'),
            'initial_amount' => data_get($session, 'amount_total', 0) / 100,
            'total_amount' => data_get($session, 'amount_total', 0) / 100,
            // 'expected_delivery_date' => isset($metadata['expected_delivery_date']) ? Carbon::parse($metadata['expected_delivery_date'])->toDateString() : null,
            'vendor_id' => $vendorId,
            'product_subtotal' => $productSubtotalMeta !== null ? (float) $productSubtotalMeta : null,
            'storage_cost_applied' => $storageCostAppliedMeta !== null ? (float) $storageCostAppliedMeta : null,
        ]);
    }

    private function processOrderLineItems(Order $mainOrder, \Stripe\Collection $lineItemsFromStripe, array $itemDetailsLookup): void
    {
        // Convert itemDetailsLookup to indexed array to match line item positions
        $itemDetailsArray = array_values($itemDetailsLookup);

        foreach ($lineItemsFromStripe->data as $index => $lineItem) {
            $stripePriceId = $lineItem->price->id;
            $product = null;
            // #! BIG ASSUMPTION: line items are in the same order as itemDetailsLookup
            $itemDetail = $itemDetailsArray[$index] ?? null;

            if ($stripePriceId) {
                $product = Product::where('stripe_price_id', $stripePriceId)->first();
            }

            if ($product) {
                $this->createOrderLineItemForProduct($mainOrder, $lineItem, $product, $itemDetail);
            } else {
                $lineItemDescription = strtolower($lineItem->description ?? '');

                // Scenario 3: Storage fee
                if ($lineItemDescription === 'storage fee') {
                    $this->createStorageFeeLineItem($mainOrder, $lineItem, $itemDetailsArray);
                }
                // Scenario 2: Product with custom pricing (no stripe_price_id match)
                elseif ($itemDetail && isset($itemDetail['product_id'])) {
                    $customProduct = Product::find($itemDetail['product_id']);
                    if ($customProduct) {
                        $this->createOrderLineItemForProduct($mainOrder, $lineItem, $customProduct, $itemDetail);
                    }
                }
            }
        }
    }

    private function createOrderLineItemForProduct(Order $mainOrder, $lineItem, Product $product, ?array $itemDetail): void
    {
        $purchasePoolId = data_get($itemDetail, 'purchase_pool_id');
        $purchaseCycleId = data_get($itemDetail, 'purchase_cycle_id');

        if (is_null($purchasePoolId)) {
            info("Webhook Warning: Product ID {$product->id} missing 'purchase_pool_id'.", ['order_id' => $mainOrder->id]);
        }
        if (is_null($purchaseCycleId)) {
            info("Webhook Warning: Product ID {$product->id} missing 'purchase_cycle_id'.", ['order_id' => $mainOrder->id]);
        }

        OrderLineItem::create([
            'order_id' => $mainOrder->id,
            'product_id' => $product->id,
            'quantity' => $lineItem->quantity,
            'price_per_unit' => ($lineItem->price->unit_amount / 100),
            'total_price' => ($lineItem->amount_total / 100),
            'purchase_pool_id' => $purchasePoolId,
        ]);

        // Update purchase pool volume
        if ($purchasePoolId && $activePool = PurchasePool::find($purchasePoolId)) {
            $activePool->increment('current_volume', $lineItem->quantity);
        }

        // Update cycle product volume
        if ($purchaseCycleId && $currentCycle = PurchaseCycle::find($purchaseCycleId)) {
            $cycleProductVolume = CycleProductVolume::firstOrCreate(
                ['purchase_cycle_id' => $currentCycle->id, 'product_id' => $product->id],
                ['total_aggregated_quantity' => 0]
            );
            $cycleProductVolume->increment('total_aggregated_quantity', $lineItem->quantity);
        }
    }

    private function createStorageFeeLineItem(Order $mainOrder, $lineItem, array $itemDetailsArray): void
    {
        $firstProductDetails = reset($itemDetailsArray);
        $purchasePoolId = data_get($firstProductDetails, 'purchase_pool_id');

        OrderLineItem::create([
            'order_id' => $mainOrder->id,
            'product_id' => null,
            'description' => 'Storage Fee',
            'quantity' => $lineItem->quantity,
            'price_per_unit' => ($lineItem->amount_total / 100),
            'total_price' => ($lineItem->amount_total / 100),
            'purchase_pool_id' => $purchasePoolId,
        ]);

        Log::info('Webhook: Processed storage fee line item for order.', [
            'order_id' => $mainOrder->id,
            'amount' => ($lineItem->amount_total / 100),
        ]);
    }

    private function processStorageOrder(Order $mainOrder, array $metadata, string $sessionId): void
    {
        $storageOrderId = data_get($metadata, 'storage_order_id');
        $storageNeededFlag = filter_var(data_get($metadata, 'storage_needed_flag', false), FILTER_VALIDATE_BOOLEAN);

        if ($storageOrderId && $storageNeededFlag) {
            $storageOrder = StorageOrder::find($storageOrderId);
            if ($storageOrder) {
                $storageOrder->update([
                    'order_id' => $mainOrder->id,
                    'status' => 'pending_arrival',
                ]);
                Log::info('StorageOrder linked and updated successfully.', ['storage_order_id' => $storageOrderId, 'main_order_id' => $mainOrder->id]);
                // TODO: Notify admin event
            } else {
                Log::warning('StorageOrder ID in metadata, but record not found.', ['storage_order_id' => $storageOrderId, 'session_id' => $sessionId]);
            }
        } elseif ($storageNeededFlag && ! $storageOrderId) {
            Log::error('Storage needed, but no storage_order_id provided in metadata.', ['session_id' => $sessionId, 'metadata' => $metadata]);
        }
    }
}
