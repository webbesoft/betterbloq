import { Badge } from '@/components/ui/badge';
import { Card } from '@/components/ui/card';
import { formatPrice } from '@/helpers/orders-helper';
import { OrderLineItem } from '@/types/model-types';
import { PackageIcon, PercentIcon } from 'lucide-react';

type Props = {
    line_items: OrderLineItem[];
};

const OrderLineItemsList = (props: Props) => {
    const { line_items } = props;

    return (
        <Card className={'bg-background rounded-sm p-4 shadow-sm'}>
            <h2 className="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                <PackageIcon className="h-5 w-5" /> Product Details
            </h2>
            <div className="divide-y divide-gray-200 dark:divide-gray-700">
                {' '}
                {line_items.map((line_item: OrderLineItem) => {
                    const product = line_item.product;
                    if (!product) return null;

                    return (
                        <div key={line_item.id} className="grid grid-cols-1 items-center gap-4 py-4 md:grid-cols-4 lg:grid-cols-5">
                            {product.image && (
                                <div className="flex justify-center md:col-span-1">
                                    {' '}
                                    <div className="h-16 w-16 overflow-hidden rounded-md">
                                        {' '}
                                        <img src={product.image} alt={product.name} className="h-full w-full object-cover" />
                                    </div>
                                </div>
                            )}

                            <div className="space-y-1 md:col-span-1 lg:col-span-2">
                                <p className="font-medium text-gray-900 dark:text-gray-100">{product.name}</p>
                                <div className="flex flex-wrap items-center gap-2">
                                    {' '}
                                    {/* Use flex-wrap for smaller screens */}
                                    <Badge variant="secondary" className="text-xs">
                                        {product.category}
                                    </Badge>
                                    {/* Display Purchase Pool Discount if available */}
                                    {line_item.purchase_pool && line_item.purchase_pool.discount_percentage > 0 && (
                                        <Badge variant="default" color="green" className="text-xs">
                                            {' '}
                                            {/* Use a variant that indicates a benefit */}
                                            <PercentIcon className="mr-1 h-3 w-3" />
                                            {line_item.purchase_pool.discount_percentage}% Pool Discount
                                            {/* Optional: Link to pool details if needed */}
                                            {/* {line_item.purchase_pool.id && (
                                                <Link href={`/purchase-pools/${line_item.purchase_pool.id}`} className="ml-1 underline">
                                                    View
                                                </Link>
                                            )} */}
                                        </Badge>
                                    )}
                                    {/* Optional: Display Pool Name if available */}
                                    {line_item.purchase_pool.name && (
                                        <Badge variant="outline" className="text-xs">
                                            Pool: {line_item.purchase_pool.name}
                                        </Badge>
                                    )}
                                </div>
                            </div>

                            {/* Quantity */}
                            <div className="flex items-center gap-2 md:col-span-1">
                                <span className="text-sm font-medium text-gray-700 dark:text-gray-300">Qty:</span>
                                <p className="text-gray-900 dark:text-gray-100">{line_item.quantity}</p>
                            </div>

                            {/* Price Per Unit and Total Price */}
                            <div className="space-y-1 md:col-span-1">
                                <div className="flex items-center gap-2">
                                    <span className="text-sm font-medium text-gray-700 dark:text-gray-300">Price:</span>
                                    <p className="text-gray-900 dark:text-gray-100">{formatPrice(line_item.price_per_unit)}</p>
                                </div>
                                <div className="flex items-center gap-2">
                                    <span className="text-sm font-medium text-gray-700 dark:text-gray-300">Total:</span>
                                    <p className="font-semibold text-gray-900 dark:text-gray-100">{formatPrice(line_item.total_price)}</p>
                                </div>
                            </div>
                        </div>
                    );
                })}
            </div>
        </Card>
    );
};

export default OrderLineItemsList;
