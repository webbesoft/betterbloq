import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Separator } from '@/components/ui/separator';
import { useCartStore } from '@/stores/use-cart-store';
import { Link } from '@inertiajs/react';
import { ShoppingCart, Trash2 } from 'lucide-react';

export function CartDropdown() {
    const { items, currentVendorId, removeItem, updateQuantity } = useCartStore();

    const cartItemCount = items.reduce((sum, item) => sum + item.quantity, 0);
    const firstItemVendorName = items[0]?.vendor_name;

    const handleQuantityChange = (productId: number | string, newQuantity: number) => {
        const qty = Math.max(0, Number(newQuantity));
        if (isNaN(qty)) return;

        if (qty === 0) {
            removeItem(productId);
        } else {
            updateQuantity(productId, qty);
        }
    };

    const calculateSubtotal = () => {
        return items.reduce((total, item) => total + Number(item.final_line_price), 0);
    };

    return (
        <Popover>
            <PopoverTrigger asChild>
                <Button variant="outline" size="icon" className="relative">
                    <ShoppingCart className="h-5 w-5" />
                    {cartItemCount > 0 && (
                        <Badge
                            variant="destructive"
                            className="absolute -top-2 -right-2 flex h-5 w-7 items-center justify-center rounded-full p-0 text-xs"
                        >
                            {cartItemCount}
                        </Badge>
                    )}
                    <span className="sr-only">Open shopping cart</span>
                </Button>
            </PopoverTrigger>
            <PopoverContent className="z-100 w-96" align="end">
                <div className="p-1">
                    {items.length === 0 ? (
                        <p className="text-muted-foreground text-center">Your cart is empty.</p>
                    ) : (
                        <>
                            {/* Header Info */}
                            <div className="mb-4 space-y-1 text-sm">
                                {/* Display Vendor Name if available, otherwise Vendor ID */}
                                <p className="font-medium">
                                    Vendor: <span className="text-muted-foreground">{firstItemVendorName ?? `ID ${currentVendorId}`}</span>
                                </p>
                                {/* <div className="text-muted-foreground flex items-center gap-1">
                                    <CalendarDays className="h-4 w-4" />
                                    <span>Est. Delivery:</span>
                                    <span className="text-foreground font-medium">{formattedDeliveryDate}</span>
                                </div> */}
                            </div>

                            <Separator className="mb-4" />

                            <ScrollArea className="-mr-4 max-h-[300px] pr-4">
                                <div className="space-y-4">
                                    {items.map((item) => (
                                        <div key={item.id} className="flex items-center gap-3">
                                            {item.image && (
                                                <img
                                                    src={item.image}
                                                    alt={item.name}
                                                    className="h-12 w-12 rounded border object-cover"
                                                    onError={(e) => {
                                                        (e.target as HTMLImageElement).src = 'https://placehold.co/100x100/e2e8f0/cbd5e0?text=Thumb';
                                                    }}
                                                />
                                            )}
                                            <div className="flex-grow">
                                                <p className="truncate text-sm font-medium">{item.name}</p>
                                                <p className="text-muted-foreground truncate text-xs">
                                                    <span className="font-medium">Exp. delivery: </span>
                                                    {item.expected_delivery_date}
                                                </p>
                                                <p className="text-muted-foreground text-xs">${item.price.toFixed(2)}</p>
                                            </div>
                                            <div className="flex shrink-0 items-center gap-1">
                                                <Input
                                                    type="number"
                                                    min="0"
                                                    value={item.quantity}
                                                    onChange={(e) => handleQuantityChange(item.id, parseInt(e.target.value))}
                                                    className="h-8 w-12 px-1 text-center"
                                                    aria-label={`Quantity for ${item.name}`}
                                                />
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    className="text-muted-foreground hover:text-destructive h-8 w-8"
                                                    onClick={() => removeItem(item.id)}
                                                    aria-label={`Remove ${item.name}`}
                                                >
                                                    <Trash2 className="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </ScrollArea>

                            <Separator className="my-4" />

                            <div className="mb-4 flex items-center justify-between">
                                <span className="text-sm font-semibold">Subtotal:</span>
                                <span className="text-sm font-bold">${calculateSubtotal().toFixed(2)}</span>
                            </div>

                            <Link href={route('cart.show')}>
                                <Button className="w-full">View Cart & Checkout</Button>
                            </Link>
                        </>
                    )}
                </div>
            </PopoverContent>
        </Popover>
    );
}
