import { Head, Link, usePage } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Progress } from '@/components/ui/progress';
import { format } from 'date-fns';
import {
    CalendarIcon,
    CheckCircleIcon, Flag,
    HomeIcon,
    PackageIcon,
    ShoppingCartIcon,
    StoreIcon,
    TagIcon,
    TruckIcon,
    UserIcon
} from 'lucide-react';
import { Order } from '@/types/model-types';
import AppLayout from '@/layouts/app-layout';
import { Card } from '@/components/ui/card';
// Import for the round chart (you might need to install this: npm install react-chartjs-2 chart.js)
// import { PieChart } from 'react-chartjs-2';
// import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';

// ChartJS.register(ArcElement, Tooltip, Legend);

interface ShowOrderProps {
    order: {
        data: Order;
    };
}

export default function OrderView(props: ShowOrderProps) {
    const { data } = props.order;

    const purchasePoolProgress = data.purchase_pool
        ? (parseFloat(data.purchase_pool.current_volume) / parseFloat(data.purchase_pool.target_volume)) * 100
        : 0;

    const getOrderStatusBadge = (status: string) => {
        switch (status.toLowerCase()) {
            case 'pending':
                return <Badge variant="secondary">{status}</Badge>;
            case 'processing':
                return <Badge variant="default">{status}</Badge>;
            case 'completed':
                return <Badge variant="outline">{status}</Badge>;
            case 'cancelled':
                return <Badge variant="destructive">{status}</Badge>;
            default:
                return <Badge>{status}</Badge>;
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

    return (
        <AppLayout breadcrumbs={[{
            title: 'Orders',
            href: route('orders.index')
        }]}>
            <Head title={"Order #" + data.id} />
            <div className="mb-2 flex justify-between items-center p-4">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100">Order Details</h1>
                    <p className="text-gray-500 dark:text-gray-400">Order ID: #{data.id}</p>
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 p-4">
                {/* Main Content Area */}
                <div className="lg:col-span-2 space-y-8">
                    {/* Order Summary */}
                    <Card className={'shadow-xs rounded-sm p-4 bg-background'}>
                        <h2 className="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                            <TagIcon className="h-5 w-5" /> Order Summary
                        </h2>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div className="flex items-center gap-2">
                                <span className="font-medium text-gray-700 dark:text-gray-300">Status:</span>
                                {getOrderStatusBadge(data.status)}
                            </div>
                            <div className="flex items-center gap-2">
                                <span className="font-medium text-gray-700 dark:text-gray-300">Quantity:</span>
                                {data.quantity}
                            </div>
                            <div className="flex items-center gap-2">
                                <span className="font-medium text-gray-700 dark:text-gray-300">Payment:</span>
                                <Badge variant="outline" className="flex items-center gap-1">
                                    <CheckCircleIcon className="h-4 w-4" /> Paid
                                </Badge>
                            </div>
                            {/* Add created_at if you include it in the resource */}
                            {/* <div className="flex items-center gap-2">
                                <span className="font-medium text-gray-700 dark:text-gray-300">Order Date:</span>
                                {data.created_at && format(new Date(data.created_at), 'yyyy-MM-dd HH:mm')}
                            </div> */}
                        </div>
                    </Card>

                    {/* Product Details */}
                    <Card className={'shadow-sm rounded-sm p-4 bg-background'}>
                        <h2 className="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                            <PackageIcon className="h-5 w-5" /> Product Details
                        </h2>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
                            {data.product?.image && (
                                <div className="rounded-md overflow-hidden w-48 h-48">
                                    <img src={data.product.image} alt={data.product.name} className="w-full h-full object-cover" />
                                </div>
                            )}
                            <div className="space-y-2">
                                <div className="flex items-center gap-2">
                                    <span className="font-medium text-gray-700 dark:text-gray-300">Name:</span>
                                    <p className="text-gray-900 dark:text-gray-100">{data.product?.name}</p>
                                </div>
                                <div className="flex items-center gap-2">
                                    <span className="font-medium text-gray-700 dark:text-gray-300">Category:</span>
                                    <Badge variant="secondary">{data.product?.category}</Badge>
                                </div>
                                <div className="flex items-center gap-2">
                                    <span className="font-medium text-gray-700 dark:text-gray-300">Price:</span>
                                    <p className="text-gray-900 dark:text-gray-100">${data.product?.price} / {data.product?.unit}</p>
                                </div>
                            </div>
                        </div>
                    </Card>

                    {/* Purchase Pool Details */}
                    <Card className={'shadow-sm rounded-sm p-4 bg-background'}>
                        <h2 className="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center justify-between gap-2">
                            <div className={'flex items-center justify-start gap-4'}>
                                <ShoppingCartIcon className="h-5 w-5" /> Purchase Pool Details
                            </div>
                            <Button variant={'default'}>
                                <Link href={route('purchase-pools.show', data.purchase_pool!.id)} variant="outline">
                                    View Purchase Pool
                                </Link>
                            </Button>
                        </h2>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div className="flex items-center gap-2">
                                <CalendarIcon className="h-4 w-4 text-gray-500 dark:text-gray-400" />
                                <span className="font-medium text-gray-700 dark:text-gray-300">End Date:</span>
                                <p className="text-gray-900 dark:text-gray-100">{format(new Date(data.purchase_pool!.end_date), 'MM/dd/yyyy')}</p>
                            </div>
                            <div className="flex items-center gap-2">
                                <TruckIcon className="h-4 w-4 text-gray-500 dark:text-gray-400" />
                                <span className="font-medium text-gray-700 dark:text-gray-300">Target Delivery:</span>
                                <p className="text-gray-900 dark:text-gray-100">{format(new Date(data.purchase_pool!.target_delivery_date), 'MM/dd/yyyy')}</p>
                            </div>
                            <div className="col-span-full flex justify-center items-center h-32">
                                <div className="w-24 h-24 relative">
                                    <svg viewBox="0 0 100 100">
                                        <circle cx="50" cy="50" r="45" stroke="#e5e7eb" strokeWidth="10" fill="transparent" />
                                        <circle
                                            cx="50"
                                            cy="50"
                                            r="45"
                                            stroke="#22c55e"
                                            strokeWidth="10"
                                            fill="transparent"
                                            strokeDasharray={`${Math.PI * 90}`}
                                            strokeDashoffset={`${Math.PI * 90 * (1 - purchasePoolProgress / 100)}`}
                                            strokeLinecap="round"
                                            transform="rotate(-90 50 50)"
                                        />
                                        <text x="50" y="55" textAnchor="middle" className="font-semibold text-sm text-gray-700 dark:text-gray-300">
                                            {purchasePoolProgress.toFixed(0)}%
                                        </text>
                                    </svg>
                                </div>
                            </div>
                            <div className="col-span-full flex justify-center text-sm text-gray-500 dark:text-gray-400">
                                {data.purchase_pool?.current_volume} / {data.purchase_pool?.target_volume}
                            </div>
                            {data.purchase_pool?.min_orders_for_discount > 0 && (
                                <div className="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                    <span className="font-medium">Min Orders for Discount:</span>
                                    {data.purchase_pool?.min_orders_for_discount}
                                </div>
                            )}
                            {data.purchase_pool?.max_orders === 0 ? (
                                <div className="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                    <span className="font-medium">Max Orders:</span>
                                    <Badge variant="secondary">Unlimited</Badge>
                                </div>
                            ) : (
                                <div className="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                    <span className="font-medium">Max Orders:</span>
                                    {data.purchase_pool?.max_orders}
                                </div>
                            )}
                        </div>
                    </Card>
                </div>

                {/* Side Column */}
                <aside className="space-y-8">
                    {/* Customer Information */}
                    <Card className={'shadow-sm rounded-sm p-4 bg-background'}>
                        <h2 className="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
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
                                <span className="font-medium flex items-center gap-2 text-gray-700 dark:text-gray-300">
                                    <HomeIcon className="h-4 w-4" /> Address:
                                </span>
                                <p className="text-gray-900 dark:text-gray-100">
                                    {data.address?.line1 && <div>{data.address.line1}</div>}
                                    {data.address?.line2 && <div>{data.address.line2}</div>}
                                    {data.address?.city && <span>{data.address.city}, </span>}
                                    {data.address?.state && <span>{data.address.state} </span>}
                                    {data.address?.postal_code && <span>{data.address.postal_code}</span>}
                                    {data.address?.country && <div>Country: {data.address.country}</div>}
                                    {!data.address?.line1 && !data.address?.city && !data.address?.country && (
                                        <span className="text-gray-500 dark:text-gray-400">No address provided</span>
                                    )}
                                </p>
                            </div>
                        </div>
                    </Card>

                    {/* Vendor Information */}
                    <Card className={'shadow-sm rounded-sm p-4 bg-background'}>
                        <h2 className="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                            <StoreIcon className="h-5 w-5" /> Vendor Information
                        </h2>
                        <div className="space-y-2">
                            <div className="flex items-center gap-2">
                                <span className="font-medium text-gray-700 dark:text-gray-300">Name:</span>
                                <p className="text-gray-900 dark:text-gray-100">{data.vendor?.name}</p>
                            </div>
                        </div>
                    </Card>
                </aside>
            </div>
        </AppLayout>
    );
}
