import { Button } from '@/components/ui/button';
import LandingLayout from '@/layouts/landing-layout';
import { useCartStore } from '@/stores/use-cart-store';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { ShoppingCart } from 'lucide-react';
import { useEffect } from 'react';
import { toast } from 'sonner';
import CartItemComponent from './components/cart/cart-item';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Market',
        href: '/market',
    },
];

interface CheckoutForm {
    items: {
        product_id: number;
        quantity: number;
        expected_delivery_date: string;
        purchase_cycle_id?: number;
        requires_storage_acknowledged?: boolean;
        final_line_price: string;
        storage_cost_applied?: string;
        daily_storage_price?: string;
        product_subtotal: string;
    }[];
}

export default function CartPage(props) {
    const { items, expectedDeliveryDate, removeItem, updateQuantity, clearCart } = useCartStore();
    const { data, setData, post, processing, errors, reset } = useForm<CheckoutForm>({
        items: [],
    });

    const handleQuantityChange = (productId: number | string, newQuantity: number) => {
        const qty = Math.max(0, Number(newQuantity));
        if (isNaN(qty)) return;

        if (qty === 0) {
            if (window.confirm('Remove this item from the cart?')) {
                removeItem(productId);
            }
        } else {
            updateQuantity(productId, qty);
        }
    };

    const calculateTotalStorageCost = () => {
        return items.reduce((total, item) => {
            return total + (Number(item.storage_cost_applied) || 0);
        }, 0);
    };

    const calculateSubtotal = () => {
        return items.reduce((total, item) => {
            const itemSubtotal = Number(item.product_subtotal) || Number(item.final_line_price);
            return total + itemSubtotal;
        }, 0);
    };

    const formatDate = (date: Date | null): string => {
        if (!date) return 'N/A';

        if (typeof date !== 'object') {
            const asDate = new Date(date);

            return asDate.toLocaleDateString(undefined, {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
            });
        }

        return date.toLocaleDateString(undefined, {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    };

    const handleCheckout = (e: React.FormEvent) => {
        e.preventDefault();

        if (items.length === 0) {
            toast.error('Your cart is empty');
            return;
        }

        post(route('orders.store'), {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Orders placed successfully!');
                clearCart();
            },
            onError: (formErrors) => {
                console.error('Checkout error:', formErrors);
                toast.error('Failed to place orders. Please try again.');
            },
        });
    };

    useEffect(() => {
        // Transform cart items to the format expected by the backend
        const orderItems = items.map((item) => ({
            product_id: item.id,
            quantity: item.quantity,
            expected_delivery_date: item.expected_delivery_date || '',
            purchase_cycle_id: item.purchase_cycle_id,
            requires_storage_acknowledged: (Number(item.storage_cost_applied) || 0) > 0,
            final_line_price: item.final_line_price || (Number(item.price) * item.quantity).toFixed(2),
            storage_cost_applied: item.storage_cost_applied || '0.00',
            daily_storage_price: Number(item.daily_storage_price).toFixed(2) || Number('0.00').toFixed(2),
            product_subtotal: item.product_subtotal || (Number(item.final_line_price || item.price) * item.quantity).toFixed(2),
        }));

        setData('items', orderItems);
    }, [items]);

    return (
        <LandingLayout breadcrumbs={breadcrumbs}>
            <Head title="My Cart" />
            <div className="container mx-auto p-4">
                <h1 className="mb-4 text-center text-2xl font-bold">Your Shopping Cart</h1>

                {items.length === 0 ? (
                    <div className="flex flex-col items-center justify-center rounded-lg border-2 border-dashed p-12 text-center">
                        <div className="bg-muted mb-4 rounded-full p-4">
                            <ShoppingCart className="text-muted-foreground h-12 w-12" />
                        </div>
                        <h3 className="text-xl font-semibold">Your Shopping Cart is Empty</h3>
                        <p className="text-muted-foreground mt-2 mb-6">Browser our offering to find something you'll love.</p>
                        <Button>
                            <a className="" href={route('market')}>
                                Continue Shopping
                            </a>
                        </Button>
                    </div>
                ) : (
                    <div>
                        {/* <p className="mb-4 text-sm text-gray-600">
                            Items from Vendor: <span className="font-semibold">{currentVendorName}</span>
                        </p> */}

                        {/* Cart Items List */}
                        <div className="mb-6 space-y-4">
                            {items.map((item) => (
                                <CartItemComponent key={item.id} item={item} onQuantityChange={handleQuantityChange} onRemoveItem={removeItem} />
                            ))}
                        </div>

                        {/* Cart Summary & Delivery */}
                        <div className="rounded-lg bg-gray-100 p-4">
                            <div className="mb-2 flex items-center justify-between">
                                <span className="font-semibold">Items Subtotal:</span>
                                <span className="text-lg font-bold">${calculateSubtotal().toFixed(2)}</span>
                            </div>
                            {calculateTotalStorageCost() > 0 && (
                                <div className="mb-2 flex items-center justify-between">
                                    <span className="font-semibold">Storage Costs:</span>
                                    <span className="text-lg font-bold">${calculateTotalStorageCost().toFixed(2)}</span>
                                </div>
                            )}
                            <div className="mb-4 flex items-center justify-between border-t pt-2">
                                <span className="text-lg font-bold">Total:</span>
                                <span className="text-xl font-bold">${(calculateSubtotal() + calculateTotalStorageCost()).toFixed(2)}</span>
                            </div>
                            <Button className="mt-4 w-full" onClick={handleCheckout} disabled={processing || items.length === 0}>
                                {processing ? 'Placing Orders...' : 'Proceed to Checkout'}
                            </Button>
                            <Button variant="outline" onClick={clearCart} className="mt-2 w-full" disabled={processing}>
                                Clear Cart
                            </Button>
                        </div>
                    </div>
                )}
            </div>
        </LandingLayout>
    );
}
