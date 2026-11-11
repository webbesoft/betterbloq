import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { CartItem } from '@/types/cart';
import { CalendarDays, Package, Trash2 } from 'lucide-react';
import { useState } from 'react';

export interface CartItemProps {
    item: CartItem;
    onQuantityChange: (id: number | string, quantity: number) => void;
    onRemoveItem: (id: number | string) => void;
}

export default function CartItemComponent({ item, onQuantityChange, onRemoveItem }: CartItemProps) {
    const [imageFailed, setImageFailed] = useState(false);
    const itemSubtotal = Number(item.product_subtotal) || Number(item.final_line_price || item.price) * item.quantity;
    const storage = Number(item.storage_cost_applied) || 0;
    const delivery = item.expected_delivery_date
        ? new Date(`${item.expected_delivery_date.slice(0, 10)}T12:00:00`).toLocaleDateString(undefined, {
              month: 'short',
              day: 'numeric',
              year: 'numeric',
          })
        : 'Not set';

    return (
        <article className="surface p-4 sm:p-5">
            <div className="flex items-start gap-4 sm:gap-5">
                <div className="bg-muted/60 flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-xl border sm:size-24">
                    {item.image && !imageFailed ? (
                        <img src={item.image} alt={item.name} className="size-full object-cover" onError={() => setImageFailed(true)} />
                    ) : (
                        <Package className="text-muted-foreground/50 size-8 stroke-1" />
                    )}
                </div>
                <div className="min-w-0 flex-1">
                    <p className="text-muted-foreground truncate text-[11px]">{item.vendor_name}</p>
                    <h2 className="mt-1 text-base font-semibold tracking-tight">{item.name}</h2>
                    <p className="text-muted-foreground mt-1.5 text-xs">
                        ${Number(item.price).toFixed(2)} / {item.unit}
                    </p>
                    <p className="text-muted-foreground mt-3 flex items-center gap-1.5 text-[11px]">
                        <CalendarDays className="size-3.5 shrink-0" />
                        Expected delivery: {delivery}
                    </p>
                </div>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    onClick={() => onRemoveItem(item.id)}
                    aria-label={`Remove ${item.name}`}
                    className="text-muted-foreground hover:text-destructive -mt-1 -mr-1 size-8 shrink-0"
                >
                    <Trash2 className="size-4" />
                </Button>
            </div>
            <div className="mt-5 flex items-center justify-between gap-4 border-t pt-4">
                <div className="flex items-center gap-3">
                    <label htmlFor={`cart-quantity-${item.id}`} className="text-muted-foreground text-xs">
                        Quantity
                    </label>
                    <Input
                        id={`cart-quantity-${item.id}`}
                        type="number"
                        min="0"
                        value={item.quantity}
                        onChange={(event) => onQuantityChange(item.id, parseInt(event.target.value))}
                        className="bg-background/60 h-9 w-20 rounded-lg text-center text-sm"
                    />
                </div>
                <div className="text-right">
                    <p className="text-lg font-semibold tracking-tight tabular-nums">${(itemSubtotal + storage).toFixed(2)}</p>
                    {storage > 0 && <p className="text-muted-foreground mt-0.5 text-[10px]">Includes ${storage.toFixed(2)} estimated storage</p>}
                </div>
            </div>
        </article>
    );
}
