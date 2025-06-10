import { CalculationResult, Product, Warehouse } from '@/types/model-types';

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
     * Converts a dimension from its source unit to meters.
     * TODO: use a robust library like 'convert-units'.
     */
    private convertToFeet(value: number, fromUnit: 'cm' | 'm' | 'in' | 'ft'): number {
        if (isNaN(value) || value <= 0) return 0;
        switch (fromUnit) {
            case 'cm':
                return value / 30.48;
            case 'm':
                return value / 0.3048;
            case 'in':
                return value / 12;
            case 'ft':
            default:
                return value;
        }
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
     * Calculates the product's footprint in SQUARE FEET.
     */
    public getFootprintInSqFt(): number {
        if (!this.product.storable) return 0;
        const length = this.convertToFeet(this.product.default_length!, this.product.storage_unit_of_measure!);
        const width = this.convertToFeet(this.product.default_width!, this.product.storage_unit_of_measure!);
        return length * width;
    }

    /**
     * Calculates the product's volume in CUBIC FEET.
     */
    public getVolumeInCuFt(): number {
        if (!this.product.storable) return 0;
        const height = this.convertToFeet(this.product.default_height!, this.product.storage_unit_of_measure!);
        return this.getFootprintInSqFt() * height;
    }

    /**
     * Calculates daily price from ANY provided price and period.
     * Note: Approximating month as 30 days. For financial accuracy, handle monthly billing separately.
     */
    public getDailyPrice(price: number, period: 'days' | 'weeks' | 'months'): number {
        if (isNaN(price) || price < 0) return 0;
        switch (period) {
            case 'days':
                return price;
            case 'weeks':
                return price / 7;
            case 'months':
                return price / 30; // Approximation
            default:
                return 0;
        }
    }

    /**
     * The main calculation method to get a full pricing breakdown for a given quantity.
     *
     * @param quantity - The number of product units to be stored.
     * @returns A detailed pricing object for the UI.
     */
    public calculatePrice(quantity: number): CalculationResult | null {
        if (!this.product.storable || quantity <= 0) {
            return null;
        }

        // 1. Calculate the total space required based on the warehouse's pricing unit.
        const spaceUnit = this.warehouse.total_capacity_unit;
        let requiredSpace = 0;
        if (spaceUnit === 'sq ft') {
            requiredSpace = this.getFootprintInSqFt() * quantity;
        } else {
            // 'cu ft'
            requiredSpace = this.getVolumeInCuFt() * quantity;
        }

        // 2. Find the applicable storage tier based on the required space.
        const tiers = this.warehouse.available_tiers || [];
        const appliedTier = tiers.find((t) => requiredSpace >= t.min_space_units && requiredSpace < t.max_space_units) || null;

        // 3. Determine the correct rate and billing period to use.
        const isUsingDefaultRate = !appliedTier;
        const pricePerSpaceUnit = appliedTier ? appliedTier.price_per_space_unit : this.warehouse.default_storage_price_per_unit;
        const billingPeriod = appliedTier ? appliedTier.billing_period : this.warehouse.default_storage_price_period;

        // 4. Calculate the total cost for the period, accounting for stackability.
        let costPerPeriod = 0;
        if (spaceUnit === 'sq ft') {
            // For area-based pricing, we calculate the cost of the footprint.
            // Stacking allows more items on the same footprint, effectively reducing per-item cost.
            const footprintPerUnit = this.getFootprintInSqFt();
            const stackSize = this.product.is_stackable && this.product.max_stack_height_units! > 1 ? this.product.max_stack_height_units! : 1;

            // Calculate how many physical stacks are needed
            const numberOfStacks = Math.ceil(quantity / stackSize);
            const totalFootprintNeeded = footprintPerUnit * numberOfStacks;
            costPerPeriod = totalFootprintNeeded * pricePerSpaceUnit;
        } else {
            // 'cu ft'
            // For volume-based pricing, stacking is already part of the volume.
            costPerPeriod = requiredSpace * pricePerSpaceUnit;
        }

        let costPerDay = 0;
        switch (billingPeriod) {
            case 'days':
                costPerDay = costPerPeriod;
                break;
            case 'weeks':
                costPerDay = costPerPeriod / 7;
                break;
            case 'months':
                costPerDay = costPerPeriod / 30;
                break; // Approximation
        }

        return {
            requiredSpace: requiredSpace,
            spaceUnit: spaceUnit,
            appliedTier: appliedTier,
            isUsingDefaultRate: isUsingDefaultRate,
            costPerPeriod: costPerPeriod,
            costPerItemPerPeriod: costPerPeriod / quantity,
            billingPeriod: billingPeriod,
            costPerDay: costPerDay,
        };
    }
}
