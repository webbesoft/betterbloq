import { Product, Warehouse } from '@/types/model-types';
import { useEffect, useMemo, useState } from 'react';

// Helper function to add days to a date
const addDays = (date: string, days: number) => {
    const result = new Date(date);
    result.setDate(result.getDate() + days);
    return result;
};

const convertToDailyPrice = (price: number, period: string) => {
    if (!price || !period) return 0;
    switch (period.toLowerCase()) {
        case 'hours':
            return price * 24;
        case 'days':
            return price;
        case 'weeks':
            return price / 7;
        case 'months':
            return price / 30;
        default:
            console.warn(`Unknown storage price period: ${period}`);
            return 0;
    }
};

interface ProductPriceCalculatorProps {
    product: Product;
    storageProvider: Warehouse;
    initialQuantity: number;
    purchaseDate: Date;
    onStateChange: any;
    minimumDeliveryDate: string;
}

export const ProductPriceCalculator = (props: ProductPriceCalculatorProps) => {
    const { product, initialQuantity, onStateChange, minimumDeliveryDate, storageProvider } = props;
    const [quantity, setQuantity] = useState(initialQuantity);
    const baseExpectedDeliveryDate = useMemo(() => {
        const deliveryTime = product.delivery_time ?? 0;
        const prepTime = product.vendor?.prep_time ?? 0;
        return addDays(minimumDeliveryDate, deliveryTime + prepTime);
    }, [minimumDeliveryDate, product.delivery_time, product.vendor?.prep_time]);

    const [chosenDeliveryDate, setChosenDeliveryDate] = useState(() => {
        return new Date(baseExpectedDeliveryDate);
    });

    const handleQuantityChange = (event) => {
        const newQuantity = parseInt(event.target.value, 10);
        setQuantity(newQuantity > 0 ? newQuantity : 1);
    };

    const handleDateChange = (event) => {
        setChosenDeliveryDate(new Date(event.target.value));
    };

    // Calculate storage details
    const { storageDays, dailyStoragePrice, totalStorageCost, requiresStorage } = useMemo(() => {
        // Use the 'storageProvider' prop here
        if (!storageProvider || !chosenDeliveryDate) {
            return { storageDays: 0, dailyStoragePrice: 0, totalStorageCost: 0, requiresStorage: false };
        }

        // Normalize dates to compare day parts only (already done by YYYY-MM-DD parsing to UTC midnight)
        const startOfChosenDate = new Date(chosenDeliveryDate.getFullYear(), chosenDeliveryDate.getMonth(), chosenDeliveryDate.getDate());
        const startOfExpectedDate = new Date(
            baseExpectedDeliveryDate.getFullYear(),
            baseExpectedDeliveryDate.getMonth(),
            baseExpectedDeliveryDate.getDate(),
        );

        const diffInMilliseconds = startOfChosenDate.getTime() - startOfExpectedDate.getTime();
        const diffInDays = diffInMilliseconds / (1000 * 60 * 60 * 24);

        let currentStorageDays = 0;
        let currentRequiresStorage = false;

        if (diffInDays > 3) {
            currentRequiresStorage = true;
            // Ensure storage days is not negative if logic changes or dates are unexpected
            currentStorageDays = Math.max(0, Math.ceil(diffInDays - 3));
        }

        const currentDailyStoragePrice = convertToDailyPrice(
            storageProvider.default_storage_price_per_unit,
            storageProvider.default_storage_price_period,
        );

        const currentTotalStorageCost = currentStorageDays * currentDailyStoragePrice * quantity;

        return {
            storageDays: currentStorageDays,
            dailyStoragePrice: currentDailyStoragePrice,
            totalStorageCost: currentTotalStorageCost,
            requiresStorage: currentRequiresStorage,
        };
    }, [storageProvider, chosenDeliveryDate, baseExpectedDeliveryDate, quantity]); // Use storageProvider prop in dependency array

    const productSubtotal = (product.price ?? 0) * quantity;
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
                dailyStoragePrice,
            });
        }
    }, [quantity, chosenDeliveryDate, totalStorageCost, productSubtotal, finalLinePrice, requiresStorage, storageDays, dailyStoragePrice]);

    return (
        <div className="mx-auto w-full rounded-lg border bg-white p-4 shadow-md">
            {' '}
            {/* Added max-w-md for consistency */}
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
                    min="1"
                    className="w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
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
                    min={minimumDeliveryDate} // Assumes minimumDeliveryDate is 'YYYY-MM-DD'
                    className="w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
            </div>
            {storageProvider && ( // Check the prop
                <div className="mb-4 rounded-md bg-gray-50 p-3">
                    <h3 className="mb-2 text-lg font-medium text-gray-700">Storage Information</h3>
                    {requiresStorage ? (
                        <>
                            <p className="text-sm text-red-600">Your chosen delivery date requires {storageDays} day(s) of storage.</p>
                            <p className="text-sm text-gray-600">Storage Provider: {storageProvider.name}</p>
                            <p className="text-sm text-gray-600">
                                Storage cost: ${dailyStoragePrice && Number(dailyStoragePrice).toFixed(2)} per {product.unit || 'unit'} per day.
                            </p>
                        </>
                    ) : (
                        <p className="text-sm text-green-600">
                            No additional storage cost for the selected delivery date. (Up to 3 days grace period after expected delivery date of{' '}
                            {formatDate(baseExpectedDeliveryDate)})
                        </p>
                    )}
                </div>
            )}
            {/* Logic for when product is storable but no provider, or not storable */}
            {!storageProvider && product.storable && (
                <p className="mb-4 rounded-md bg-yellow-50 p-3 text-sm text-yellow-600">
                    Storage information is not available for this product, or no preferred provider is assigned.
                </p>
            )}
            {!product.storable && ( // Assuming product has a 'storable' boolean field
                <p className="mb-4 rounded-md bg-blue-50 p-3 text-sm text-blue-600">This product does not require storage.</p>
            )}
            <div className="mt-6 border-t pt-4">
                <div className="text-md mb-1 flex justify-between">
                    <span>
                        Product Subtotal ({quantity} {product.unit || 'item(s)'}):
                    </span>
                    <span className="font-medium">${productSubtotal.toFixed(2)}</span>
                </div>
                {requiresStorage && storageProvider && (
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
