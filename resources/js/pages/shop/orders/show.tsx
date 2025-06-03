import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { formatPrice, getOrderPaymentStatusText } from '@/helpers/orders-helper';
import AppLayout from '@/layouts/app-layout';
import { Order } from '@/types/model-types';
import { Head } from '@inertiajs/react';
import { format } from 'date-fns';
import {
    Ban,
    CalendarIcon,
    CheckCircle,
    CheckCircleIcon,
    CircleDot,
    CreditCard,
    DownloadIcon,
    HomeIcon,
    Hourglass,
    Loader,
    ShoppingCartIcon,
    TagIcon,
    TruckIcon,
    UserIcon,
    XCircle,
} from 'lucide-react';
import OrderLineItemsList from '../components/order/order-line-items';

interface ShowOrderProps {
    order: {
        data: Order;
    };
}

export default function OrderView(props: ShowOrderProps) {
    const { data } = props.order;

    // const purchasePoolProgress = data.purchase_pool
    //     ? (parseFloat(data.purchase_pool.current_volume) / parseFloat(data.purchase_pool.target_volume)) * 100
    //     : 0;

    const getOrderStatusBadge = (status: string) => {
        // options: 'created', 'payment_authorized', 'pending_finalization', 'processing_capture', 'completed', 'capture_failed', 'cancelled'
        switch (status.toLowerCase()) {
            case 'created':
                return (
                    <Badge variant="default">
                        <CircleDot className="mr-1 h-3 w-3" />
                        Created
                    </Badge>
                );
            case 'payment_authorized':
                return (
                    <Badge variant="secondary">
                        <CreditCard className="mr-1 h-3 w-3" />
                        Authorized
                    </Badge>
                );
            case 'pending_finalization':
                return (
                    <Badge variant="outline">
                        <Hourglass className="mr-1 h-3 w-3" />
                        Pending Finalization
                    </Badge>
                );
            case 'processing_capture':
                return (
                    <Badge variant="secondary">
                        <Loader className="mr-1 h-3 w-3 animate-spin" />
                        Processing Capture
                    </Badge>
                );
            case 'completed':
                return (
                    <Badge variant="default" color="green">
                        {' '}
                        <CheckCircle className="mr-1 h-3 w-3" />
                        Completed
                    </Badge>
                );
            case 'capture_failed':
                return (
                    <Badge variant="destructive">
                        <XCircle className="mr-1 h-3 w-3" />
                        Failed
                    </Badge>
                );
            case 'cancelled':
                return (
                    <Badge variant="destructive">
                        <Ban className="mr-1 h-3 w-3" />
                        Cancelled
                    </Badge>
                );
            default:
                return (
                    <Badge variant="secondary">
                        {' '}
                        {/* Default or unknown status */}
                        <CircleDot className="mr-1 h-3 w-3" />
                        Unknown
                    </Badge>
                );
        }
    };

    const getPurchasePoolStatusBadge = (status: string) => {
        switch (status.toLowerCase()) {
            case 'active':
                return <Badge className="bg-green-500 text-white">{status}</Badge>;
            case 'pending':
                return <Badge variant="secondary">{status}</Badge>;
            case 'completed':
                return <Badge variant="outline">{status}</Badge>;
            case 'cancelled':
                return <Badge variant="destructive">{status}</Badge>;
            default:
                return <Badge>{status}</Badge>;
        }
    };

    const getUniquePurchasePools = (lineItems) => {
        if (!lineItems || !Array.isArray(lineItems)) {
            return [];
        }
        const uniquePools = [];
        const poolIds = new Set();
        lineItems.forEach((item) => {
            if (item.purchase_pool && !poolIds.has(item.purchase_pool.id)) {
                poolIds.add(item.purchase_pool.id);
                uniquePools.push(item.purchase_pool);
            }
        });
        return uniquePools;
    };

    const uniquePurchasePools = getUniquePurchasePools(data.line_items);

    return (
        <AppLayout
            breadcrumbs={[
                {
                    title: 'Orders',
                    href: route('orders.index'),
                },
                {
                    title: `Order #${data.id}`,
                    href: route('orders.show', data.id), // Assuming a route for individual order
                },
            ]}
        >
            <Head title={'Order #' + data.id} />
            <div className="mb-2 flex items-center justify-between p-4">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100">Order Details</h1>
                    <p className="text-gray-500 dark:text-gray-400">Order ID: #{data.id}</p>
                </div>

                <div>
                    <a href={route('invoice.orders.download', data.id)} target="_blank" rel="noopener noreferrer">
                        <Button variant="outline" size="sm">
                            <DownloadIcon className="mr-2 h-4 w-4" />
                            Download & Email Invoice
                        </Button>
                    </a>
                </div>
            </div>

            <div className="grid grid-cols-1 gap-8 p-4 lg:grid-cols-3">
                {/* Main Content Area */}
                <div className="space-y-8 lg:col-span-2">
                    {/* Order Summary */}
                    <Card className={'bg-background rounded-sm p-4 shadow-xs'}>
                        <h2 className="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                            <TagIcon className="h-5 w-5" /> Order Summary
                        </h2>
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div className="flex items-center gap-2">
                                <span className="font-medium text-gray-700 dark:text-gray-300">Status:</span>
                                {getOrderStatusBadge(data.status)}
                            </div>
                            {/* Quantity was commented out, assuming it's per line_item now or not needed here */}
                            <div className="flex items-center gap-2">
                                <span className="font-medium text-gray-700 dark:text-gray-300">Payment:</span>
                                <Badge variant="outline" className="flex items-center gap-1">
                                    <CheckCircleIcon className="h-4 w-4" /> {getOrderPaymentStatusText(data.status)}
                                </Badge>
                            </div>
                            {data.created_at && (
                                <div className="flex items-center gap-2">
                                    <span className="font-medium text-gray-700 dark:text-gray-300">Order Date:</span>
                                    {format(new Date(data.created_at), 'yyyy-MM-dd HH:mm')}
                                </div>
                            )}
                        </div>
                    </Card>
                    {/* Product Details */}
                    <OrderLineItemsList line_items={data.line_items} />
                    {data.total_order_price !== undefined && (
                        <div className="mt-4 flex justify-end border-t border-gray-200 pt-4 dark:border-gray-700">
                            <div className="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                                <span>Order Total:</span>
                                <span>{formatPrice(data.total_order_price)}</span>
                            </div>
                        </div>
                    )}
                    {/* Purchase Pool Details - Updated Section */}
                    {uniquePurchasePools.length > 0 ? (
                        uniquePurchasePools.map((pool, index) => {
                            const purchasePoolProgress = pool.target_volume > 0 ? (pool.current_volume / pool.target_volume) * 100 : 0;
                            return (
                                <Card key={pool.id || index} className={'bg-background mt-8 rounded-sm p-4 shadow-sm'}>
                                    <h2 className="mb-4 flex items-center justify-between gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        <div className={'flex items-center justify-start gap-4'}>
                                            <ShoppingCartIcon className="h-5 w-5" />
                                            {pool.name ? `Purchase Pool: ${pool.name}` : `Purchase Pool ID: #${pool.id}`}
                                        </div>
                                        {/* {pool.id && ( // Only show button if pool.id exists
                                            <Button variant={'default'} size={'sm'} asChild>
                                                <Link href={route('purchase-pools.show', pool.id)}>View Purchase Pool</Link>
                                            </Button>
                                        )} */}
                                    </h2>
                                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                        {/* Target Delivery Date */}
                                        {pool.target_delivery_date && (
                                            <div className="flex items-center gap-2">
                                                <TruckIcon className="h-4 w-4 text-gray-500 dark:text-gray-400" />
                                                <span className="font-medium text-gray-700 dark:text-gray-300">Target Delivery:</span>
                                                <p className="text-gray-900 dark:text-gray-100">
                                                    {format(new Date(pool.target_delivery_date), 'MM/dd/yyyy')}
                                                </p>
                                            </div>
                                        )}

                                        {/* Current Tier & Discount */}
                                        {pool.purchase_pool_tier && (
                                            <div className="flex items-center gap-2">
                                                <TagIcon className="h-4 w-4 text-gray-500 dark:text-gray-400" />
                                                <span className="font-medium text-gray-700 dark:text-gray-300">Current Tier:</span>
                                                <p className="text-gray-900 dark:text-gray-100">
                                                    {pool.purchase_pool_tier.name || 'N/A'}
                                                    {pool.purchase_pool_tier.discount_percentage !== undefined && (
                                                        <Badge variant="secondary" className="ml-2">
                                                            {pool.purchase_pool_tier.discount_percentage}% off
                                                        </Badge>
                                                    )}
                                                    {/* You can add other forms of discount display here, e.g., fixed amount */}
                                                </p>
                                            </div>
                                        )}

                                        {/* Pool End Date (if available) */}
                                        {pool.end_date && (
                                            <div className="flex items-center gap-2">
                                                <CalendarIcon className="h-4 w-4 text-gray-500 dark:text-gray-400" />
                                                <span className="font-medium text-gray-700 dark:text-gray-300">Pool End Date:</span>
                                                <p className="text-gray-900 dark:text-gray-100">{format(new Date(pool.end_date), 'MM/dd/yyyy')}</p>
                                            </div>
                                        )}

                                        {/* Progress Circle Area */}
                                        {pool.current_volume !== undefined && pool.target_volume !== undefined && pool.target_volume > 0 && (
                                            <>
                                                <div className="col-span-full flex h-32 items-center justify-center">
                                                    <div className="relative h-24 w-24">
                                                        <svg viewBox="0 0 100 100">
                                                            <circle cx="50" cy="50" r="45" stroke="#e5e7eb" strokeWidth="10" fill="transparent" />
                                                            <circle
                                                                cx="50"
                                                                cy="50"
                                                                r="45"
                                                                stroke="#22c55e" // green-500
                                                                strokeWidth="10"
                                                                fill="transparent"
                                                                strokeDasharray={`${Math.PI * 2 * 45}`} // Circumference
                                                                strokeDashoffset={`${Math.PI * 2 * 45 * (1 - purchasePoolProgress / 100)}`}
                                                                strokeLinecap="round"
                                                                transform="rotate(-90 50 50)"
                                                            />
                                                            <text
                                                                x="50"
                                                                y="55"
                                                                textAnchor="middle"
                                                                className="text-sm font-semibold text-gray-700 dark:text-gray-300"
                                                            >
                                                                {purchasePoolProgress.toFixed(0)}%
                                                            </text>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div className="col-span-full flex justify-center text-sm text-gray-500 dark:text-gray-400">
                                                    {pool.current_volume} / {pool.target_volume} units
                                                </div>
                                            </>
                                        )}

                                        {/* Min Orders/Units for Discount Tier (if applicable) */}
                                        {pool.min_orders_for_discount !== undefined && pool.min_orders_for_discount > 0 && (
                                            <div className="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                                <span className="font-medium">Min Units for Discount:</span>
                                                {pool.min_orders_for_discount}
                                            </div>
                                        )}

                                        {/* Max Orders/Units in Pool (if applicable) */}
                                        {pool.max_orders !== undefined &&
                                            (pool.max_orders === 0 ? (
                                                <div className="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                                    <span className="font-medium">Max Units in Pool:</span>
                                                    <Badge variant="outline">Unlimited</Badge>
                                                </div>
                                            ) : (
                                                <div className="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                                    <span className="font-medium">Max Units in Pool:</span>
                                                    {pool.max_orders}
                                                </div>
                                            ))}
                                    </div>
                                </Card>
                            );
                        })
                    ) : (
                        <Card className={'bg-background mt-8 rounded-sm p-4 shadow-sm'}>
                            <h2 className="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                                <ShoppingCartIcon className="h-5 w-5" /> Purchase Pool Details
                            </h2>
                            <p className="text-gray-500 dark:text-gray-400">No purchase pool information associated with items in this order.</p>
                        </Card>
                    )}
                </div>

                {/* Side Column */}
                <aside className="space-y-8">
                    {/* Customer Information */}
                    <Card className={'bg-background rounded-sm p-4 shadow-sm'}>
                        <h2 className="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                            <UserIcon className="h-5 w-5" /> Customer Information
                        </h2>
                        <div className="space-y-2">
                            <div className="flex items-center gap-2">
                                <span className="font-medium text-gray-700 dark:text-gray-300">Email:</span>
                                <p className="text-gray-900 dark:text-gray-100">{data.email}</p>
                            </div>
                            <div className="flex items-center gap-2">
                                <span className="font-medium text-gray-700 dark:text-gray-300">Phone:</span>
                                <p className="text-gray-900 dark:text-gray-100">{data.phone}</p>
                            </div>
                            <div>
                                <span className="flex items-center gap-2 font-medium text-gray-700 dark:text-gray-300">
                                    <HomeIcon className="h-4 w-4" /> Address:
                                </span>
                                <p className="mt-1 text-gray-900 dark:text-gray-100">
                                    {data.address?.line1 && <div>{data.address.line1}</div>}
                                    {data.address?.line2 && <div>{data.address.line2}</div>}
                                    {data.address?.city && <span>{data.address.city}, </span>}
                                    {data.address?.state && <span>{data.address.state} </span>}
                                    {data.address?.postal_code && <span>{data.address.postal_code}</span>}
                                    {data.address?.country && <div className="mt-1">Country: {data.address.country}</div>}
                                    {!data.address?.line1 && !data.address?.city && !data.address?.country && (
                                        <span className="text-gray-500 dark:text-gray-400">No address provided</span>
                                    )}
                                </p>
                            </div>
                        </div>
                    </Card>

                    {/* Vendor Information (if needed, was commented out) */}
                    {/* <Card className={'bg-background rounded-sm p-4 shadow-sm'}>
                        <h2 className="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                            <StoreIcon className="h-5 w-5" /> Vendor Information
                        </h2>
                        <div className="space-y-2">
                            <div className="flex items-center gap-2">
                                <span className="font-medium text-gray-700 dark:text-gray-300">Name:</span>
                                <p className="text-gray-900 dark:text-gray-100">{data.vendor?.name}</p>
                            </div>
                        </div>
                    </Card> */}
                </aside>
            </div>
        </AppLayout>
    );
}
