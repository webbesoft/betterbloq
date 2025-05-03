import React from 'react';
import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Separator } from '@/components/ui/separator';
import { Badge } from '@/components/ui/badge';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { formatDate } from '@/lib/helpers'; 
import { useCartStore } from '@/stores/use-cart-store';
import { ShoppingCart, CalendarDays, Trash2 } from 'lucide-react';

export function CartDropdown() {
  const {
    items,
    currentVendorId,
    expectedDeliveryDate,
    removeItem,
    updateQuantity,
  } = useCartStore();

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
    return items.reduce((total, item) => total + item.price * item.quantity, 0);
  };

  const formattedDeliveryDate = expectedDeliveryDate ? formatDate(expectedDeliveryDate.toString()) : 'N/A';

  return (
    <Popover>
      <PopoverTrigger asChild>
        <Button variant="outline" size="icon" className="relative">
          <ShoppingCart className="h-5 w-5" />
          {cartItemCount > 0 && (
            <Badge
              variant="destructive"
              className="absolute -top-2 -right-2 h-5 w-5 p-0 flex items-center justify-center rounded-full text-xs"
            >
              {cartItemCount}
            </Badge>
          )}
          <span className="sr-only">Open shopping cart</span>
        </Button>
      </PopoverTrigger>
      <PopoverContent className="w-80 z-100" align="end">
        <div className="p-4">
          {items.length === 0 ? (
            <p className="text-center text-muted-foreground">Your cart is empty.</p>
          ) : (
            <>
              {/* Header Info */}
              <div className="mb-4 space-y-1 text-sm">
                 {/* Display Vendor Name if available, otherwise Vendor ID */}
                 <p className="font-medium">
                    Vendor: <span className='text-muted-foreground'>{firstItemVendorName ?? `ID ${currentVendorId}`}</span>
                 </p>
                 <div className="flex items-center gap-1 text-muted-foreground">
                    <CalendarDays className="h-4 w-4" />
                    <span>Est. Delivery:</span>
                    <span className="font-medium text-foreground">{formattedDeliveryDate}</span>
                 </div>
              </div>

              <Separator className="mb-4" />

              <ScrollArea className="max-h-[300px] pr-4 -mr-4"> 
                <div className="space-y-4">
                  {items.map((item) => (
                    <div key={item.id} className="flex items-center gap-3">
                      {item.image && (
                         <img
                           src={item.image}
                           alt={item.name}
                           className="w-12 h-12 object-cover rounded border"
                         />
                      )}
                      <div className="flex-grow">
                        <p className="text-sm font-medium truncate">{item.name}</p>
                        <p className="text-xs text-muted-foreground">${item.price.toFixed(2)}</p>
                      </div>
                      <div className="flex items-center gap-1 shrink-0">
                        <Input
                          type="number"
                          min="0"
                          value={item.quantity}
                          onChange={(e) => handleQuantityChange(item.id, parseInt(e.target.value))}
                          className="w-12 h-8 text-center px-1" 
                          aria-label={`Quantity for ${item.name}`}
                        />
                        <Button
                          variant="ghost"
                          size="icon"
                          className="h-8 w-8 text-muted-foreground hover:text-destructive"
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

              <div className="flex justify-between items-center mb-4">
                <span className="text-sm font-semibold">Subtotal:</span>
                <span className="text-sm font-bold">${calculateSubtotal().toFixed(2)}</span>
              </div>

              <Link href={route('cart.view')} > 
                  <Button className="w-full">
                      View Cart & Checkout
                  </Button>
              </Link>
            </>
          )}
        </div>
      </PopoverContent>
    </Popover>
  );
}