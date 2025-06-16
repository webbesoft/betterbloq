import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { CartItem } from '@/types/cart';
import { Trash2 } from 'lucide-react';

export interface CartItemProps {
    item: CartItem;
    onQuantityChange: any;
    onRemoveItem: any;
}

export default function CartItemComponent(props: CartItemProps) {
    const { item, onQuantityChange, onRemoveItem } = props;

    const formatDate = (dateString: string | undefined) => {
        if (!dateString) return 'Not set';
        const date = new Date(dateString);
        return date.toLocaleDateString(undefined, {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        });
    };

    const itemSubtotal = Number(item.product_subtotal) || Number(item.final_line_price || item.price) * item.quantity;
    const storagePerItem = Number(item.storage_cost_applied) || 0;
    const totalStorageForItem = storagePerItem * item.quantity;

    return (
        <div key={item.id} className="rounded-lg border bg-white p-4 shadow-sm">
            <div className="flex items-start justify-between">
                <div className="flex items-start space-x-4">
                    {item.image && (
                        <img
                            src={item.image}
                            alt={item.name}
                            className="h-20 w-20 rounded-lg object-cover"
                            onError={(e) => {
                                (e.target as HTMLImageElement).src = 'https://placehold.co/100x100/e2e8f0/cbd5e0?text=Image+Not+Available';
                            }}
                        />
                    )}
                    <div className="flex-1">
                        <h3 className="text-lg font-semibold">{item.name}</h3>
                        <p className="text-sm text-gray-600">{item.vendor_name}</p>

                        <div className="mt-2 space-y-1">
                            <div className="flex items-center gap-4 text-sm">
                                <span className="text-gray-600">Unit Price:</span>
                                <span className="font-medium">
                                    ${Number(item.price).toFixed(2)} / {item.unit}
                                </span>
                            </div>

                            <div className="flex items-center gap-4 text-sm">
                                <span className="text-gray-600">Expected Delivery:</span>
                                <span className="font-medium">{formatDate(item.expected_delivery_date)}</span>
                            </div>

                            {totalStorageForItem > 0 && (
                                <div className="flex items-center gap-4 text-sm">
                                    <span className="text-gray-600">Storage Cost:</span>
                                    <span className="font-medium text-amber-600">
                                        ${storagePerItem.toFixed(2)} per unit × {item.quantity} = ${totalStorageForItem.toFixed(2)}
                                    </span>
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                <div className="text-right">
                    <div className="mb-3">
                        <div className="text-sm text-gray-600">Item Total</div>
                        <div className="text-xl font-bold">${(itemSubtotal + totalStorageForItem).toFixed(2)}</div>
                    </div>

                    <div className="mx-auto flex items-end space-x-2">
                        <div className="text-center">
                            <div className="mb-1 text-xs text-gray-500">Quantity</div>
                            <Input
                                type="number"
                                min="0"
                                value={item.quantity}
                                onChange={(e) => onQuantityChange(item.id, parseInt(e.target.value))}
                                className="h-9 w-16 text-center"
                            />
                        </div>
                        <Button
                            variant="outline"
                            size="icon"
                            onClick={() => onRemoveItem(item.id)}
                            aria-label={`Remove ${item.name}`}
                            className="h-9 w-9"
                        >
                            <Trash2 className="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    );
}
