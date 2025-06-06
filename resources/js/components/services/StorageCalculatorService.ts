import { Product, Warehouse } from '@/types/model-types';

export class StorageCalculatorService {
    private product: Product;
    private warehouse: Warehouse;

    constructor(product: Product, warehouse: Warehouse) {
        if (!product) {
            throw new Error('Product must be provided to StorageCalculatorService.');
        }
        if (!warehouse) {
            throw new Error('Warehouse must be provided to StorageCalculatorService.');
        }
        this.product = product;
        this.warehouse = warehouse;
    }

    /**
     * Safely gets a product dimension, returning 0 if invalid.
     * Assumes dimensions are in a unit compatible with warehouse pricing (e.g., meters).
     */
    private getProductDimension(value: number | undefined | null, dimensionName: string): number {
        const numValue = Number(value);
        if (isNaN(numValue) || numValue <= 0) {
            console.warn(`Product '${this.product.name}' has invalid or non-positive ${dimensionName}: ${value}. Using 0 for calculation.`);
            return 0;
        }
        return numValue;
    }

    /**
     * Calculates the volume of a single unit of the product.
     * Returns volume (e.g., in cubic meters).
     */
    public getVolumePerProductUnit(): number {
        if (!this.product.storable) return 0;
        const length = this.getProductDimension(this.product.default_length, 'length');
        const width = this.getProductDimension(this.product.default_width, 'width');
        const height = this.getProductDimension(this.product.default_height, 'height');

        if (length === 0 || width === 0 || height === 0) {
            console.warn(`Product '${this.product.name}' has one or more zero dimensions, resulting in zero volume.`);
            return 0;
        }
        return length * width * height;
    }

    /**
     * Calculates the footprint (area) of a single unit of the product.
     * Returns area (e.g., in square meters).
     */
    public getFootprintPerProductUnit(): number {
        if (!this.product.storable) return 0;
        const length = this.getProductDimension(this.product.default_length, 'length');
        const width = this.getProductDimension(this.product.default_width, 'width');

        if (length === 0 || width === 0) {
            console.warn(`Product '${this.product.name}' has zero length or width, resulting in zero footprint.`);
            return 0;
        }
        return length * width;
    }

    /**
     * Converts the warehouse's storage price for its specific space unit (e.g., cbm, sqm) to a daily rate.
     * Returns price per warehouse space unit per day (e.g., $/cbm/day or $/sqm/day).
     */
    public getDailyPricePerWarehouseSpaceUnit(): number {
        const price = Number(this.warehouse.default_storage_price_per_unit);
        const period = this.warehouse.default_storage_price_period;

        if (isNaN(price) || price < 0 || !period) {
            console.warn(
                `Warehouse '${this.warehouse.name}' has invalid base pricing data (price: ${this.warehouse.default_storage_price_per_unit}, period: ${period}). Defaulting to 0.`,
            );
            return 0;
        }

        switch (period.toLowerCase()) {
            case 'hours':
                return price * 24;
            case 'days':
                return price;
            case 'weeks':
                return price / 7;
            case 'months':
                return price / 30; // Using 30 days as an approximation for a month
            default:
                console.warn(`Warehouse '${this.warehouse.name}' uses an unknown storage price period: ${period}. Cannot convert to daily rate.`);
                return 0;
        }
    }

    /**
     * Calculates the storage cost PER PRODUCT UNIT PER DAY.
     * This considers product dimensions, stackability, and warehouse pricing model.
     */
    public getStorageCostPerProductUnitPerDay(): number {
        if (!this.product.storable) return 0;

        const dailyPricePerWarehouseSpaceUnit = this.getDailyPricePerWarehouseSpaceUnit();
        if (dailyPricePerWarehouseSpaceUnit === 0) {
            // Warning would have been logged by getDailyPricePerWarehouseSpaceUnit
            return 0;
        }

        const warehousePricingBasis = this.warehouse.total_capacity_unit?.toLowerCase();

        switch (warehousePricingBasis) {
            case 'cu ft': {
                // Warehouse prices per Cubic Foot
                const volumePerUnit = this.getVolumePerProductUnit();
                if (volumePerUnit === 0) return 0; // Avoid cost for zero-volume items
                // Cost per product unit per day = volume_of_one_product * price_per_cbm_per_day
                return volumePerUnit * dailyPricePerWarehouseSpaceUnit;
            }

            case 'cu yd': {
                // Warehouse prices per Cubic Foot
                const volumePerUnit = this.getVolumePerProductUnit();
                if (volumePerUnit === 0) return 0; // Avoid cost for zero-volume items
                // Cost per product unit per day = volume_of_one_product * price_per_cbm_per_day
                return volumePerUnit * dailyPricePerWarehouseSpaceUnit;
            }

            case 'cu m': {
                // Warehouse prices per Cubic Foot
                const volumePerUnit = this.getVolumePerProductUnit();
                if (volumePerUnit === 0) return 0; // Avoid cost for zero-volume items
                // Cost per product unit per day = volume_of_one_product * price_per_cbm_per_day
                return volumePerUnit * dailyPricePerWarehouseSpaceUnit;
            }

            case 'sq ft': {
                // Warehouse prices per Square foot
                const footprintPerUnit = this.getFootprintPerProductUnit();
                if (footprintPerUnit === 0) return 0; // Avoid cost for zero-footprint items

                let itemsPerFootprintSlot = 1;
                if (this.product.is_stackable) {
                    const stackHeightUnits = Number(this.product.max_stack_height_units);
                    if (!isNaN(stackHeightUnits) && stackHeightUnits > 0) {
                        itemsPerFootprintSlot = stackHeightUnits;
                    } else {
                        console.warn(
                            `Product '${this.product.name}' is stackable but has invalid or zero max_stack_height_units (${this.product.max_stack_height_units}). Assuming a stack of 1 for pricing.`,
                        );
                    }
                }
                // Cost for the footprint = footprint_area * daily_price_per_sqm_per_day
                // This cost is for a stack occupying that footprint.
                // So, cost per item in that stack = (cost_for_footprint_area) / number_of_items_in_stack_on_that_footprint
                return (footprintPerUnit * dailyPricePerWarehouseSpaceUnit) / itemsPerFootprintSlot;
            }

            default:
                // Fallback: If warehouse pricing unit is not 'cbm' or 'sqm' (e.g., it's 'item_unit', null, or something else).
                // Assume 'default_storage_price_per_unit' is per product item.
                console.warn(
                    `Warehouse '${this.warehouse.name}' prices per '${warehousePricingBasis || 'undefined unit'}'. ` +
                        `This scheme doesn't use product L/W/H directly like 'cbm' or 'sqm'. ` +
                        `Falling back to interpret 'default_storage_price_per_unit' as per-item price.`,
                );
                // In this fallback, getDailyPricePerWarehouseSpaceUnit() is effectively the daily price per item.
                return dailyPricePerWarehouseSpaceUnit;
        }
    }
}
