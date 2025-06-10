/**
 * Calculates the actual date storage charges will begin based on prep times.
 *
 * @param purchaseDate - The date the product order was finalized.
 * @param productPrepTimeInDays - The product's specific prep time (e.g., product.delivery_time).
 * @param vendorPrepTimeInDays - Any additional prep time from the vendor.
 * @returns The calculated start date for billing.
 */
export function calculateStorageStartDate(purchaseDate: Date, productPrepTimeInDays: number, vendorPrepTimeInDays: number): Date {
    const startDate = new Date(purchaseDate);
    const totalPrepTime = (productPrepTimeInDays || 0) + (vendorPrepTimeInDays || 0);
    startDate.setDate(startDate.getDate() + totalPrepTime);
    return startDate;
}
