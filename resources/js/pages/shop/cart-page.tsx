import { Button } from '@/components/ui/button';
import LandingLayout from '@/layouts/landing-layout';
import { useCartStore } from '@/stores/use-cart-store';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, ArrowRight, LockKeyhole, ShoppingBag, ShoppingCart } from 'lucide-react';
import { useEffect } from 'react';
import { toast } from 'sonner';
import CartItemComponent from './components/cart/cart-item';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Cart',
        href: '/cart',
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

export default function CartPage() {
    const { items, removeItem, updateQuantity, clearCart } = useCartStore();
    const { setData, post, processing, errors } = useForm<CheckoutForm>({
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
            <Head title="Your project cart" />
            <div className="shop-container pt-8 sm:pt-10">
                <div className="mb-9 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
                    <div>
                        <p className="page-eyebrow mb-3">One step closer to building</p>
                        <h1 className="page-title">Your project starts here.</h1>
                        <p className="text-muted-foreground mt-4 text-sm">Review your materials before heading to checkout.</p>
                    </div>
                    <Link
                        href={route('market')}
                        className="text-muted-foreground hover:text-primary flex w-fit items-center gap-2 text-xs font-medium"
                    >
                        <ArrowLeft className="size-3.5" />
                        Back to the market
                    </Link>
                </div>
                {items.length === 0 ? (
                    <div className="surface flex flex-col items-center px-6 py-20 text-center sm:py-28">
                        <span className="bg-secondary mb-6 flex size-20 items-center justify-center rounded-sm">
                            <ShoppingBag className="text-secondary-foreground size-9 stroke-1" />
                        </span>
                        <p className="page-eyebrow mb-3">Room for something good</p>
                        <h2 className="text-2xl font-semibold tracking-tight">A solid project starts with the right materials.</h2>
                        <p className="text-muted-foreground mt-3 max-w-md text-sm leading-7">
                            Your cart is empty for now. Explore the market and find what you need for your next build.
                        </p>
                        <Button asChild className="mt-7 h-11 rounded-sm px-6">
                            <Link href={route('market')}>
                                Explore materials
                                <ArrowRight className="size-4" />
                            </Link>
                        </Button>
                    </div>
                ) : (
                    <div className="grid items-start gap-8 lg:grid-cols-[minmax(0,1fr)_360px]">
                        <section aria-label="Cart materials" className="min-w-0">
                            <div className="text-muted-foreground mb-4 flex items-center gap-2 text-xs">
                                <ShoppingCart className="size-3.5" />
                                {items.length} {items.length === 1 ? 'material' : 'materials'} in your cart
                            </div>
                            <div className="space-y-4">
                                {items.map((item) => (
                                    <CartItemComponent key={item.id} item={item} onQuantityChange={handleQuantityChange} onRemoveItem={removeItem} />
                                ))}
                            </div>
                        </section>
                        <form onSubmit={handleCheckout} className="surface overflow-hidden lg:sticky lg:top-28">
                            <div className="border-b p-6">
                                <h2 className="text-lg font-semibold tracking-tight">Project summary</h2>
                                <p className="text-muted-foreground mt-1.5 text-xs">A clear view of what’s in your cart.</p>
                            </div>
                            <div className="p-6">
                                <dl className="space-y-4 text-sm">
                                    <div className="flex justify-between gap-4">
                                        <dt className="text-muted-foreground">Materials</dt>
                                        <dd className="font-medium tabular-nums">${calculateSubtotal().toFixed(2)}</dd>
                                    </div>
                                    {calculateTotalStorageCost() > 0 && (
                                        <div className="flex justify-between gap-4">
                                            <dt className="text-muted-foreground">Estimated storage</dt>
                                            <dd className="font-medium tabular-nums">${calculateTotalStorageCost().toFixed(2)}</dd>
                                        </div>
                                    )}
                                    <div className="flex items-baseline justify-between gap-4 border-t pt-5">
                                        <dt className="font-medium">Estimated upfront total</dt>
                                        <dd className="text-2xl font-semibold tracking-tight tabular-nums">
                                            ${(calculateSubtotal() + calculateTotalStorageCost()).toFixed(2)}
                                        </dd>
                                    </div>
                                </dl>
                                <p className="text-muted-foreground mt-3 text-xs leading-5">
                                    Final prices are confirmed at checkout. Any unlocked volume discount is refunded after your purchase cycle is
                                    finalized.
                                </p>
                                {Object.values(errors).find(Boolean) && (
                                    <p role="alert" className="text-destructive bg-destructive/5 mt-4 rounded-lg p-3 text-xs leading-5">
                                        {Object.values(errors).find(Boolean)}
                                    </p>
                                )}
                                <Button
                                    type="submit"
                                    className="mt-6 h-12 w-full justify-between rounded-sm px-5"
                                    disabled={processing || items.length === 0}
                                >
                                    {processing ? 'Opening checkout…' : 'Continue to checkout'}
                                    <ArrowRight className="size-4" />
                                </Button>
                                <div className="text-muted-foreground mt-3 flex items-center justify-center gap-1.5 text-[10px]">
                                    <LockKeyhole className="size-3" />
                                    Secure checkout with Stripe
                                </div>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    onClick={clearCart}
                                    className="text-muted-foreground mt-4 h-9 w-full text-xs"
                                    disabled={processing}
                                >
                                    Clear cart
                                </Button>
                            </div>
                        </form>
                    </div>
                )}
            </div>
        </LandingLayout>
    );
}
