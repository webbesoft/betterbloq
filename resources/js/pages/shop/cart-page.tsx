import EmptyResource from '@/components/empty-resource';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Pagination, PaginationContent, PaginationItem, PaginationLink, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import { PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Slider } from '@/components/ui/slider';
import AppLayout from '@/layouts/app-layout';
import { debounce } from '@/lib/helpers';
import { type BreadcrumbItem } from '@/types';
import { PaginationBaseLinks, PaginationType, Product } from '@/types/model-types';
import { Head, router } from '@inertiajs/react';
import { Popover } from '@radix-ui/react-popover';
import { SetStateAction, useCallback, useEffect, useState } from 'react';
import ProductListItem from './components/product-list-item';
import LandingLayout from '@/layouts/landing-layout';
import ProductListItemListLayout from './components/product-list-item-list-layout';
import { ChevronDown, Trash2 } from 'lucide-react';
import { useCartStore } from '@/stores/use-cart-store';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Market',
        href: '/market',
    },
];

export default function CartPage(props) {
    const { products, filters: initialFilters, availableFilters } = props;

    const {
        items,
        currentVendorId,
        currentVendorName,
        expectedDeliveryDate,
        removeItem,
        updateQuantity,
        clearCart,
    } = useCartStore();

    const handleQuantityChange = (productId: number | string, newQuantity: number) => {
        const qty = Math.max(0, Number(newQuantity));
        if (isNaN(qty)) return;

        if (qty === 0) {
            if (window.confirm("Remove this item from the cart?")) {
                removeItem(productId);
            }
        } else {
            updateQuantity(productId, qty);
        }
    };

    const calculateSubtotal = () => {
        return items.reduce((total, item) => total + item.price * item.quantity, 0);
    };

    const formatDate = (date: Date | null): string => {
        if (!date) return 'N/A';
        return date.toLocaleDateString(undefined, {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        });
    }

    return (
        <LandingLayout breadcrumbs={breadcrumbs}>
            <Head title="My Cart" />
            <div className="container mx-auto p-4">
                <h1 className="text-2xl font-bold mb-4">Your Shopping Cart</h1>

                {items.length === 0 ? (
                    <p>Your cart is empty.</p>
                ) : (
                    <div>
                        <p className="mb-4 text-sm text-gray-600">
                            Items from Vendor: <span className="font-semibold">{currentVendorName}</span>
                        </p>

                        {/* Cart Items List */}
                        <div className="space-y-4 mb-6">
                            {items.map((item) => (
                                <div key={item.id} className="flex items-center justify-between border-b pb-4">
                                    <div className="flex items-center space-x-4">
                                        {/* Optional Image */}
                                        {item.image && <img src={item.image} alt={item.name} className="w-16 h-16 object-cover rounded" />}
                                        <div>
                                            <h3 className="font-semibold">{item.name}</h3>
                                            <p className="text-sm text-gray-500">${item.price.toFixed(2)} each</p>
                                        </div>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <Input
                                            type="number"
                                            min="0" // Allow 0 to trigger removal logic
                                            value={item.quantity}
                                            onChange={(e) => handleQuantityChange(item.id, parseInt(e.target.value))}
                                            className="w-16 h-9 text-center"
                                        />
                                        <Button
                                            variant="outline"
                                            size="icon"
                                            onClick={() => removeItem(item.id)}
                                            aria-label={`Remove ${item.name}`}
                                        >
                                            <Trash2 className="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            ))}
                        </div>

                        {/* Cart Summary & Delivery */}
                        <div className="bg-gray-100 p-4 rounded-lg">
                            <div className="flex justify-between items-center mb-2">
                                <span className="font-semibold">Subtotal:</span>
                                <span className="font-bold text-xl">${calculateSubtotal().toFixed(2)}</span>
                            </div>
                            <div className="flex justify-between items-center">
                                <span className="font-semibold">Estimated Delivery:</span>
                                <span className="font-semibold">{formatDate(expectedDeliveryDate)}</span>
                            </div>
                            {/* Add Checkout Button etc. here */}
                            <Button className="w-full mt-4">Proceed to Checkout</Button>
                            <Button variant="outline" onClick={clearCart} className="w-full mt-2">Clear Cart</Button>
                        </div>

                    </div>
                )}
            </div>
        </LandingLayout>
    );
}
