import {
    Card,
    CardHeader,
    CardTitle,
    CardContent,
    CardDescription, 
} from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge"; 
import { Link, Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { PurchasePool } from '@/types/model-types';
import {
    InfoIcon,       
    LayersIcon,     
    PercentIcon,    
    ArrowDownUpIcon,
    ListIcon,       
    BoxIcon,       
    TargetIcon,
    TrendingUpIcon,
    ShoppingCartIcon,
    CalendarIcon,
    TruckIcon,
} from 'lucide-react'; 
import { format } from 'date-fns';

interface ShowPurchasePoolProps {
    purchasePool: {
        data: PurchasePool & {
            tiers: {
                id: number;
                name?: string;
                description?: string;
                min_volume?: number | null;
                max_volume?: number | null;
                discount_percentage?: number | null;
                order: number;
            }[];
        };
    };
}

const PurchasePoolShow: React.FC<ShowPurchasePoolProps> = (props: ShowPurchasePoolProps) => {
    const { data: purchasePool } = props.purchasePool;
    const purchasePoolProgress = parseFloat(purchasePool.target_volume) > 0 ? (parseFloat(purchasePool.current_volume) / parseFloat(purchasePool.target_volume)) * 100 : 0;

    // Sort tiers by order for consistent display
    const sortedTiers = purchasePool.tiers.sort((a, b) => a.order - b.order);

    return (
        <AppLayout
            breadcrumbs={[
                { title: 'Purchase Pools', href: route('purchase-pools.index') },
                { title: purchasePool.name, href: '#' }
            ]}
        >
            <Head title={purchasePool.name} />

            {/* Header Section */}
            <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 p-4 gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100">{purchasePool.name}</h1>
                    {/* Optional: Add status badge if purchase pool has a status field */}
                    {/* <Badge variant="outline" className="mt-1">Status: {purchasePool.status || 'Unknown'}</Badge> */}
                </div>
                <div className="flex space-x-2">
                    <Button variant="outline" size="sm" disabled> {/* Disabled until implemented */}
                        {/* TODO: implement */}
                        Exit Purchase Pool
                    </Button>
                    <Link href={route('purchase-pools.index')}>
                        <Button size="sm">Back to Pools</Button>
                    </Link>
                </div>
            </div>

            <div className="grid grid-cols-1 gap-8 p-4">
                {/* Description Card */}
                {purchasePool.description && (
                    <Card className="shadow-sm bg-background">
                        <CardHeader className="flex flex-row items-center gap-2 pb-2">
                            <InfoIcon className="h-5 w-5 text-gray-500 dark:text-gray-400" />
                            <CardTitle className="text-lg font-semibold">Description</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p className="text-sm text-gray-700 dark:text-gray-300">{purchasePool.description}</p>
                        </CardContent>
                    </Card>
                )}

                {/* Purchase Pool Details */}
                <Card className={'shadow-sm rounded-sm p-4 bg-background'}>
                    <h2 className="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center justify-between gap-2">
                        <div className={'flex items-center justify-start gap-4'}>
                            <ShoppingCartIcon className="h-5 w-5" /> Purchase Pool Details
                        </div>
                    </h2>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div className="flex items-center gap-2">
                            <CalendarIcon className="h-4 w-4 text-gray-500 dark:text-gray-400" />
                            <span className="font-medium text-gray-700 dark:text-gray-300">End Date:</span>
                            <p className="text-gray-900 dark:text-gray-100">{format(new Date(purchasePool.end_date), 'MM/dd/yyyy')}</p>
                        </div>
                        <div className="flex items-center gap-2">
                            <TruckIcon className="h-4 w-4 text-gray-500 dark:text-gray-400" />
                            <span className="font-medium text-gray-700 dark:text-gray-300">Target Delivery:</span>
                            <p className="text-gray-900 dark:text-gray-100">{format(new Date(purchasePool.target_delivery_date), 'MM/dd/yyyy')}</p>
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
                            {purchasePool.current_volume} / {purchasePool.target_volume}
                        </div>
                        {purchasePool.min_orders_for_discount > 0 && (
                            <div className="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <span className="font-medium">Min Orders for Discount:</span>
                                {purchasePool.min_orders_for_discount}
                            </div>
                        )}
                        {purchasePool.max_orders === 0 ? (
                            <div className="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <span className="font-medium">Max Orders:</span>
                                <Badge variant="secondary">Unlimited</Badge>
                            </div>
                        ) : (
                            <div className="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <span className="font-medium">Max Orders:</span>
                                {purchasePool.max_orders}
                            </div>
                        )}
                    </div>
                </Card>

                {/* Tiers Section Card */}
                <Card className="shadow-sm bg-background">
                    <CardHeader className="flex flex-row items-center gap-2 pb-4">
                        <LayersIcon className="h-5 w-5 text-gray-500 dark:text-gray-400" />
                        <CardTitle className="text-lg font-semibold">Purchase Pool Tiers</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {sortedTiers.length > 0 ? (
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                {sortedTiers.map((tier) => (
                                    <Card key={tier.id} className="border bg-muted/20 dark:bg-muted/40">
                                        <CardHeader className="pb-2">
                                            <CardTitle className="text-base font-medium flex items-center justify-between">
                                                <span>
                                                    {tier.name || `Tier ${tier.order}`}
                                                </span>
                                                {tier.discount_percentage !== null && (
                                                    <Badge variant="secondary" className="flex items-center gap-1 text-xs">
                                                        <PercentIcon className="h-3 w-3" />
                                                        {tier.discount_percentage}% Off
                                                    </Badge>
                                                )}
                                            </CardTitle>
                                            {tier.description && (
                                                <CardDescription className="text-xs pt-1">
                                                    {tier.description}
                                                </CardDescription>
                                            )}
                                        </CardHeader>
                                        <CardContent className="pt-2 space-y-1">
                                            <div className="flex items-center gap-2 text-xs">
                                                <ListIcon className="h-3 w-3 text-gray-500 dark:text-gray-400" />
                                                <span className="font-medium text-gray-600 dark:text-gray-400">Order:</span>
                                                <span className="text-gray-800 dark:text-gray-200">{tier.order}</span>
                                            </div>
                                            <div className="flex items-center gap-2 text-xs">
                                                <ArrowDownUpIcon className="h-3 w-3 text-gray-500 dark:text-gray-400" />
                                                <span className="font-medium text-gray-600 dark:text-gray-400">Volume:</span>
                                                <span className="text-gray-800 dark:text-gray-200">
                                                    {tier.min_volume !== null ? tier.min_volume : 'Any'}
                                                    {' - '}
                                                    {tier.max_volume !== null ? tier.max_volume : 'Unlimited'}
                                                </span>
                                            </div>
                                            {/* Display Discount again if not shown in header or if more detail needed */}
                                            {/* <div className="flex items-center gap-2 text-xs">
                                                <PercentIcon className="h-3 w-3 text-gray-500 dark:text-gray-400" />
                                                <span className="font-medium text-gray-600 dark:text-gray-400">Discount:</span>
                                                <span className="text-gray-800 dark:text-gray-200">
                                                    {tier.discount_percentage !== null ? `${tier.discount_percentage}%` : 'N/A'}
                                                </span>
                                            </div> */}
                                        </CardContent>
                                    </Card>
                                ))}
                            </div>
                        ) : (
                            <div className="text-center py-8 text-gray-500 dark:text-gray-400">
                                No tiers defined for this purchase pool.
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Potential Future Sections (Example) */}
                {/*
                <Card className="shadow-sm bg-background">
                    <CardHeader className="flex flex-row items-center gap-2 pb-2">
                        <UsersIcon className="h-5 w-5 text-gray-500 dark:text-gray-400" />
                        <CardTitle className="text-lg font-semibold">Participants</CardTitle>
                    </CardHeader>
                    <CardContent>
                         <p className="text-sm text-gray-700 dark:text-gray-300">Participant information would go here...</p>
                    </CardContent>
                </Card>

                <Card className="shadow-sm bg-background">
                    <CardHeader className="flex flex-row items-center gap-2 pb-2">
                        <PackageIcon className="h-5 w-5 text-gray-500 dark:text-gray-400" />
                        <CardTitle className="text-lg font-semibold">Associated Product</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p className="text-sm text-gray-700 dark:text-gray-300">Product details would go here...</p>
                    </CardContent>
                </Card>
                */}

            </div>
        </AppLayout>
    );
};

export default PurchasePoolShow;