import { CartActions, CartItem, CartState } from '@/types/cart';
import { create } from 'zustand';
import { createJSONStorage, persist } from 'zustand/middleware';

const calculateDeliveryDateISO = (items: CartItem[]): string | null => {
    if (items.length === 0) {
        return null;
    }

    const maxPrepTime = items.reduce((max, item) => {
        return Math.max(max, item.preparation_time || item.preparation_time || 0);
    }, 0);

    const deliveryDate = new Date();
    deliveryDate.setDate(deliveryDate.getDate() + maxPrepTime);

    return deliveryDate.toISOString();
};

export const useCartStore = create<CartState & CartActions>()(
    persist(
        (set, get) => ({
            items: [],
            currentVendorId: null,
            currentVendorName: null,
            expectedDeliveryDate: null,

            _recalculateDeliveryDate: () => {
                set((state) => ({
                    expectedDeliveryDate: calculateDeliveryDateISO(state.items),
                }));
            },

            addItem: (product) => {
                const { items, currentVendorId } = get();
                const productVendorId = product.vendor?.id ?? product.vendor.id;
                const productVendorName = product.vendor?.name ?? product.vendor.name;

                if (!productVendorId) {
                    console.error('Product is missing Vendor ID', product);
                    return; // Or handle error appropriately
                }

                const existingItemIndex = items.findIndex((item) => item.id === product.id);

                if (currentVendorId === null || productVendorId === currentVendorId) {
                    let updatedItems: CartItem[];

                    if (existingItemIndex > -1) {
                        updatedItems = items.map((item, index) => (index === existingItemIndex ? { ...item, quantity: item.quantity + 1 } : item));
                    } else {
                        const newItem: CartItem = {
                            ...product,
                            quantity: 1,
                            vendor_id: productVendorId,
                            vendor_name: productVendorName,
                            preparation_time: product.preparation_time || product.preparation_time,
                        };
                        updatedItems = [...items, newItem];
                    }

                    set((state) => ({
                        items: updatedItems,
                        currentVendorId: state.currentVendorId ?? productVendorId,
                        currentVendorName: state.currentVendorName ?? productVendorName,
                    }));
                    get()._recalculateDeliveryDate();
                } else {
                    console.warn('addItem called with mismatched vendor. This should be handled in the component.');
                }
            },

            removeItem: (productId) => {
                let vendorId: string | number | null = null;
                let vendorName: string | null = null;
                set((state) => {
                    const newItems = state.items.filter((item) => item.id !== productId);
                    // If items remain, keep the current vendor info from the first remaining item
                    if (newItems.length > 0) {
                        vendorId = newItems[0].vendor_id ?? newItems[0].vendor.id; // Adjust based on your type
                        vendorName = newItems[0].vendor_name ?? newItems[0].vendor.name; // Adjust based on your type
                    }
                    return {
                        items: newItems,
                        currentVendorId: vendorId,
                        currentVendorName: vendorName,
                    };
                });
                // Recalculate date (will become null if items are empty)
                get()._recalculateDeliveryDate();
            },

            updateQuantity: (productId, quantity) => {
                if (quantity < 1) {
                    get().removeItem(productId); // removeItem now handles clearing vendor info if needed
                    return;
                }

                set((state) => ({
                    items: state.items.map((item) => (item.id === productId ? { ...item, quantity: quantity } : item)),
                }));
                get()._recalculateDeliveryDate();
            },

            clearCart: () => {
                set({
                    items: [],
                    currentVendorId: null,
                    currentVendorName: null, // Clear vendor name too
                    expectedDeliveryDate: null,
                });
            },
        }),
        // --- PERSISTENCE CONFIGURATION ---
        {
            name: 'shopping-cart-storage', // Key name in localStorage
            storage: createJSONStorage(() => localStorage), // Use localStorage adapter
            // Optional: You can choose which parts of the state to persist
            // partialize: (state) => ({ items: state.items, currentVendorId: state.currentVendorId, /* ... */ }),
            // Optional: Handle migration if your state shape changes over time
            // version: 1, // State version number
            // migrate: (persistedState, version) => { /* ... migration logic ... */ },
            // Note: The default JSON storage handles Date -> ISO string conversion,
            // but reading requires parsing back to Date object where needed (e.g., in components).
        },
    ), // End persist middleware
); // End create call
