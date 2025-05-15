import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import LandingLayout from '@/layouts/landing-layout';
import { useCartStore } from '@/stores/use-cart-store';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Market',
        href: '/market',
    },
];

export default function CartPage(props) {
    const { products, filters: initialFilters, availableFilters } = props;

    const { items, currentVendorId, currentVendorName, expectedDeliveryDate, removeItem, updateQuantity, clearCart } = useCartStore();

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

    const calculateSubtotal = () => {
        return items.reduce((total, item) => total + item.price * item.quantity, 0);
    };

    const formatDate = (date: Date | null): string => {
        if (!date) return 'N/A';

        if (typeof date !== 'object') {
            return date;
        }

        return date.toLocaleDateString(undefined, {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    };

    return (
        <LandingLayout breadcrumbs={breadcrumbs}>
            <Head title="My Cart" />
            <div className="container mx-auto p-4">
                <h1 className="mb-4 text-2xl font-bold">Your Shopping Cart</h1>

                {items.length === 0 ? (
                    <p>Your cart is empty.</p>
                ) : (
                    <div>
                        <p className="mb-4 text-sm text-gray-600">
                            Items from Vendor: <span className="font-semibold">{currentVendorName}</span>
                        </p>

                        {/* Cart Items List */}
                        <div className="mb-6 space-y-4">
                            {items.map((item) => (
                                <div key={item.id} className="flex items-center justify-between border-b pb-4">
                                    <div className="flex items-center space-x-4">
                                        {/* Optional Image */}
                                        {item.image && <img src={item.image} alt={item.name} className="h-16 w-16 rounded object-cover" />}
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
                                            className="h-9 w-16 text-center"
                                        />
                                        <Button variant="outline" size="icon" onClick={() => removeItem(item.id)} aria-label={`Remove ${item.name}`}>
                                            <Trash2 className="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            ))}
                        </div>

                        {/* Cart Summary & Delivery */}
                        <div className="rounded-lg bg-gray-100 p-4">
                            <div className="mb-2 flex items-center justify-between">
                                <span className="font-semibold">Subtotal:</span>
                                <span className="text-xl font-bold">${calculateSubtotal().toFixed(2)}</span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="font-semibold">Estimated Delivery:</span>
                                <span className="font-semibold">{formatDate(expectedDeliveryDate)}</span>
                            </div>
                            <Button className="mt-4 w-full">Proceed to Checkout</Button>
                            <Button variant="outline" onClick={clearCart} className="mt-2 w-full">
                                Clear Cart
                            </Button>
                        </div>
                    </div>
                )}
            </div>
        </LandingLayout>
    );
}
