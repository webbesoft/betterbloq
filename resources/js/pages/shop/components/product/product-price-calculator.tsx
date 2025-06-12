import { StorageCalculatorService } from '@/components/services/StorageCalculatorService';
import { CalculationResult, Product, PurchasePool, Warehouse } from '@/types/model-types';
import { calculateStorageStartDate } from '@/utils/date_helpers';
import { useEffect, useMemo, useState } from 'react';
import { OrderForm } from '../../product';

interface ProductPriceCalculatorProps {
    product: Product;
    storageProvider: Warehouse;
    initialQuantity: number;
    purchaseDate: Date;
    onStateChange: any;
    minimumDeliveryDate: string;
    currentPool?: PurchasePool;
    basePricePerUnit: number;
    errors: Partial<Record<keyof OrderForm, string>>;
}

export interface PriceCalculatorState {
    quantity: number;
    chosenDeliveryDate: Date;
    productSubtotal: number;
    totalStorageCost: number;
    finalLinePrice: number;
    requiresStorage: boolean;
    storageDays: number;
    dailyStoragePrice: number;
    storageCalculation: CalculationResult | null;
}

export const ProductPriceCalculator = (props: ProductPriceCalculatorProps) => {
    const { product, initialQuantity, onStateChange, minimumDeliveryDate, storageProvider, basePricePerUnit, errors } = props;
    const [quantity, setQuantity] = useState(initialQuantity);
    const baseExpectedDeliveryDate = useMemo(() => {
        const deliveryTime = product.delivery_time ?? 0;
        const prepTime = product.vendor?.prep_time ?? 0;
        return calculateStorageStartDate(new Date(minimumDeliveryDate), deliveryTime, prepTime);
    }, [minimumDeliveryDate, product.delivery_time, product.vendor?.prep_time]);

    const [chosenDeliveryDate, setChosenDeliveryDate] = useState(() => {
        return new Date(baseExpectedDeliveryDate);
    });

    const handleQuantityChange = (event: { target: { value: string } }) => {
        const newQuantity = parseInt(event.target.value, 10);
        setQuantity(newQuantity > 0 ? newQuantity : 1);
    };

    const handleDateChange = (event: { target: { value: string | number | Date } }) => {
        setChosenDeliveryDate(new Date(event.target.value));
    };

    const { storageDays, totalStorageCost, requiresStorage, storageCalculation } = useMemo(() => {
        if (!storageProvider || !product.storable) {
            return { storageDays: 0, totalStorageCost: 0, requiresStorage: false, storageCalculation: null };
        }

        // 1. Calculate the number of storage days required (Component's responsibility)
        const diffInMs = chosenDeliveryDate.getTime() - baseExpectedDeliveryDate.getTime();
        const diffInDays = Math.ceil(diffInMs / (1000 * 60 * 60 * 24));

        const GRACE_PERIOD_DAYS = 7;
        let currentStorageDays = 0;
        if (diffInDays > GRACE_PERIOD_DAYS) {
            currentStorageDays = diffInDays - GRACE_PERIOD_DAYS;
        }

        if (currentStorageDays <= 0) {
            return { storageDays: 0, totalStorageCost: 0, requiresStorage: false, storageCalculation: null };
        }

        // 2. Delegate ALL complex pricing logic to the calculator service
        try {
            const calculator = new StorageCalculatorService(product, storageProvider);
            const calcResult = calculator.calculatePrice(quantity);

            if (!calcResult) {
                return { storageDays: 0, totalStorageCost: 0, requiresStorage: false, storageCalculation: null };
            }

            // 3. The component's only job is to multiply the final daily rate by the days
            const currentTotalStorageCost = calcResult.costPerPeriod * currentStorageDays;

            return {
                storageDays: currentStorageDays,
                totalStorageCost: currentTotalStorageCost,
                requiresStorage: true,
                storageCalculation: calcResult, // Pass the rich result object to the UI
            };
        } catch (error) {
            console.error('Error during storage calculation:', error);
            return { storageDays: 0, totalStorageCost: 0, requiresStorage: false, storageCalculation: null };
        }
    }, [storageProvider, chosenDeliveryDate, baseExpectedDeliveryDate, quantity, product]);

    const productSubtotal = (basePricePerUnit ?? 0) * quantity;
    const finalLinePrice = productSubtotal + totalStorageCost;

    const formatDate = (date: Date): string => {
        if (!date || isNaN(date.getTime())) return '';
        return date.toISOString().split('T')[0];
    };

    useEffect(() => {
        if (onStateChange) {
            onStateChange({
                quantity,
                chosenDeliveryDate,
                productSubtotal,
                totalStorageCost,
                finalLinePrice,
                requiresStorage,
                storageDays,
                storageCalculation,
            });
        }
    }, [quantity, chosenDeliveryDate, totalStorageCost, productSubtotal, finalLinePrice, requiresStorage, storageDays, storageCalculation]);

    return (
        <div className="mx-auto w-full rounded-lg border bg-white p-4 shadow-md">
            {' '}
            <h2 className="mb-4 text-2xl font-semibold text-gray-800">{product.name}</h2>
            <div className="mb-4">
                <label htmlFor="quantity" className="mb-1 block text-sm font-medium text-gray-700">
                    Quantity:
                </label>
                <input
                    type="number"
                    id="quantity"
                    name="calculator_quantity"
                    value={quantity}
                    onChange={handleQuantityChange}
                    min="0"
                    className="w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
                {errors.quantity && <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.quantity}</p>}
            </div>
            <div className="mb-4">
                <p className="text-sm text-gray-600">
                    Standard delivery within: {(product.delivery_time ?? 0) + (product.vendor?.prep_time ?? 0)} days.
                </p>
                <p className="text-sm text-gray-600">
                    Expected Delivery Date (from {formatDate(new Date(minimumDeliveryDate))}): {formatDate(baseExpectedDeliveryDate)}
                </p>
            </div>
            <div className="mb-4">
                <label htmlFor="deliveryDate" className="mb-1 block text-sm font-medium text-gray-700">
                    Choose your delivery date:
                </label>
                <input
                    type="date"
                    id="deliveryDate"
                    name="calculator_delivery_date"
                    value={formatDate(chosenDeliveryDate)}
                    onChange={handleDateChange}
                    min={minimumDeliveryDate}
                    className="w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
                {errors.expected_delivery_date && <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.expected_delivery_date}</p>}
            </div>
            {storageProvider && (
                <div className="mb-4 rounded-md bg-gray-50 p-3">
                    <h3 className="mb-2 text-lg font-medium text-gray-700">Storage Information</h3>
                    {requiresStorage && storageCalculation ? (
                        <>
                            <p className="text-sm text-red-600">Your chosen delivery date requires {storageDays} day(s) of storage.</p>
                            <p className="text-sm text-gray-600">
                                Total Space Required: {storageCalculation.requiredSpace.toFixed(2)} {storageCalculation.spaceUnit}
                            </p>
                            <p className="text-sm text-gray-600">
                                Pricing Tier:{' '}
                                {storageCalculation.appliedTier
                                    ? `$${Number(storageCalculation.appliedTier.price_per_space_unit).toFixed(2)} per ${storageCalculation.spaceUnit}`
                                    : 'Default Rate'}
                            </p>
                            <p className="text-sm text-gray-600">
                                Storage cost: ${storageCalculation.costPerItemPerPeriod.toFixed(2)} per item per {storageCalculation.billingPeriod}
                            </p>
                        </>
                    ) : (
                        <p className="text-sm text-green-600">
                            No additional storage cost for the selected delivery date. (Up to 7 days grace period)
                        </p>
                    )}
                </div>
            )}
            {!storageProvider && product.storable && (
                <p className="mb-4 rounded-md bg-yellow-50 p-3 text-sm text-yellow-600">
                    Storage information is not available for this product, or no preferred provider is assigned.
                </p>
            )}
            {!product.storable && <p className="mb-4 rounded-md bg-blue-50 p-3 text-sm text-blue-600">This product does not require storage.</p>}
            <div className="mt-6 border-t pt-4">
                <div className="text-md mb-1 flex justify-between">
                    <span>
                        Product Subtotal ({quantity} {product.unit || 'item(s)'}):
                    </span>
                    <span className="font-medium">${productSubtotal.toFixed(2)}</span>
                </div>
                {requiresStorage && (
                    <div className="text-md mb-1 flex justify-between text-red-700">
                        <span>Storage Cost ({storageDays} days):</span>
                        <span className="font-medium">${totalStorageCost.toFixed(2)}</span>
                    </div>
                )}
                <div className="mt-2 flex justify-between text-xl font-bold text-gray-900">
                    <span>Total Price:</span>
                    <span>${finalLinePrice.toFixed(2)}</span>
                </div>
            </div>
        </div>
    );
};
