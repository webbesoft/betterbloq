import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { DollarSign, Eye, HelpCircle, ListChecks, MapPin, PieChart, ShoppingBag } from 'lucide-react';
import SetupGuide from '@/pages/shop/components/setup-guide';
import { useState, useEffect } from 'react';
import { Separator } from '@radix-ui/react-separator';
import EmailVerificationNotice from '@/components/EmailVerificationNotice';
import ActivePoolsProgress from './components/dashboard/active-pools-progress';
import { Button } from '@/components/ui/button';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'BetterBloq Dashboard',
        href: '/dashboard',
    },
];

interface PoolProgressData {
    id: number;
    name: string;
    product_id: number | null;
    current_volume: number;
    target_volume: number;
}

interface FrequentItem {
    product_id?: number;
    vendor_id?: number;
    name: string;
    frequency: number;
}


interface DashboardProps {
    activeOrdersCount: number,
    completedOrdersCount: number,
    ongoingProjectsCount: number,
    totalExpenses: number,
    projectBudgetSpent: any[],
    purchasePoolCompletion: any[],
    watchedPurchasePools: any[],
    frequentProducts: FrequentItem[],
    regularVendors: FrequentItem[],
    hasCompletedGuide: boolean,
    activePoolsProgress: PoolProgressData[]
}

const LOCAL_STORAGE_DISMISSED_KEY = 'setupGuideDismissed';

export default function Dashboard({
    ongoingProjectsCount,
    totalExpenses,
    projectBudgetSpent,
    purchasePoolCompletion,
    watchedPurchasePools,
    activePoolsProgress,
    frequentProducts,
    regularVendors,
    hasCompletedGuide,
    activeOrdersCount,
    completedOrdersCount
}: DashboardProps) {
    const [isGuideCompletedInDb, setIsGuideCompletedInDb] = useState<boolean>(Boolean(hasCompletedGuide) ?? false);
    const [isGuideDismissed, setIsGuideDismissed] = useState<boolean>(false);
    const { props } = usePage<{
        ziggy: {
            appEnvironment: string;
        };
    }>();
    const { appEnvironment } = props.ziggy;

    useEffect(() => {
        const dismissed = localStorage.getItem(LOCAL_STORAGE_DISMISSED_KEY) === 'true';
        setIsGuideDismissed(dismissed);
    }, []);

    useEffect(() => {
        setIsGuideCompletedInDb(hasCompletedGuide);
        if (hasCompletedGuide) {
            localStorage.removeItem(LOCAL_STORAGE_DISMISSED_KEY);
            setIsGuideDismissed(false);
        }
    }, [hasCompletedGuide]);

    const handleUpdateGuideVisibility = (visible: boolean) => {
        if (!visible) {
            localStorage.setItem(LOCAL_STORAGE_DISMISSED_KEY, 'true');
            setIsGuideDismissed(true);
        }
    };

    const handleReinstateGuide = () => {
        localStorage.removeItem(LOCAL_STORAGE_DISMISSED_KEY);
        setIsGuideDismissed(false);
    };

    const shouldShowGuide = !isGuideCompletedInDb && !isGuideDismissed;

    const shouldShowReinstateButton = !isGuideCompletedInDb && isGuideDismissed;


    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/*email verification banner*/}
                <EmailVerificationNotice />
                {/* setup guide */}
                <SetupGuide
                    visibility={shouldShowGuide} // Control visibility based on state
                    onUpdateVisibility={handleUpdateGuideVisibility}
                />
                {!shouldShowGuide && shouldShowReinstateButton && (
                    <div className="my-1 flex justify-end">
                        <Button variant="outline" size="sm" onClick={handleReinstateGuide}>
                            <HelpCircle className="mr-2 h-4 w-4" /> Show Setup Guide
                        </Button>
                    </div>
                )}
                {!shouldShowGuide && !shouldShowReinstateButton && <Separator className="my-4" />}

                <Separator />
                <div className="grid auto-rows-min gap-4 md:grid-cols-2 lg:grid-cols-3">
                    {/* Completed Orders */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                <ListChecks className="h-4 w-4" /> Completed orders
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p className="text-2xl font-bold">{completedOrdersCount ?? '...'}</p>
                        </CardContent>
                    </Card>
                    {/* Active Orders */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                <ShoppingBag className="h-4 w-4" /> Active Orders
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p className="text-2xl font-bold">{activeOrdersCount ?? '...'}</p>
                        </CardContent>
                    </Card>
                    {/* Total Expenses */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                <DollarSign className="h-4 w-4" /> Total Expenses
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p className="text-2xl font-bold">
                                ${totalExpenses?.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) ?? '0.00'}
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid auto-rows-min grid-cols-1 gap-4 lg:grid-cols-2">

                    {/* Active Pools Progress Card - Spanning full width */}
                    <div className="lg:col-span-2">
                        {/* Pass the activePoolsProgress data */}
                        {/* Ensure the component file is renamed/updated if needed */}
                        <ActivePoolsProgress pools={activePoolsProgress} />
                    </div>

                    {/* Frequent Products */}
                    <Card className="flex flex-col">
                        {/* Card Header/Content same as previous suggestion */}
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-base font-semibold">Frequently Bought</CardTitle>
                            <span className="text-muted-foreground text-xs">
                                {`(${frequentProducts.length} products)`}
                            </span>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-2 pt-2">
                            {frequentProducts.length > 0 ? (
                                <ul className="space-y-1">
                                    {frequentProducts.map((product) => (
                                        <li key={product.product_id} className="text-sm flex justify-between items-center">
                                            {product.product_id ? (
                                                <Link href={`/market/product/${product.product_id}`} className="hover:underline truncate pr-2" title={product.name}>
                                                    {product.name}
                                                </Link>
                                            ) : (
                                                <span className='pr-2 truncate'>{product.name}</span> // Handle case if ID is missing
                                            )}
                                            <span className="text-muted-foreground text-xs whitespace-nowrap">
                                                ({product.frequency} times)
                                            </span>
                                        </li>
                                    ))}
                                </ul>
                            ) : (
                                <p className="text-muted-foreground text-sm">No frequent purchase history yet.</p>
                            )}
                        </CardContent>
                    </Card>

                    {/* Regular Vendors */}
                    <Card className="flex flex-col">
                        {/* Card Header/Content same as previous suggestion */}
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-base font-semibold">Regular Vendors</CardTitle>
                            <span className="text-muted-foreground text-xs">
                                {`(${regularVendors.length} vendors)`}
                            </span>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-2 pt-2">
                            {regularVendors.length > 0 ? (
                                <ul className="space-y-1">
                                    {regularVendors.map((vendor) => (
                                        <li key={vendor.vendor_id} className="text-sm flex justify-between items-center">
                                            {vendor.vendor_id ? (
                                                <Link href={`/vendor/${vendor.vendor_id}`} className="hover:underline truncate pr-2" title={vendor.name}>
                                                    {vendor.name}
                                                </Link>
                                            ) : (
                                                <span className='pr-2 truncate'>{vendor.name}</span> // Handle case if ID is missing
                                            )}
                                            <span className="text-muted-foreground text-xs whitespace-nowrap">
                                                ({vendor.frequency} times)
                                            </span>
                                        </li>
                                    ))}
                                </ul>
                            ) : (
                                <p className="text-muted-foreground text-sm">No regular vendors yet.</p>
                            )}
                        </CardContent>
                    </Card>
                </div>

                {watchedPurchasePools.length > 0 && (
                    <div className="grid auto-rows-min gap-4 md:grid-cols-1 lg:grid-cols-1">
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Eye className="h-4 w-4" /> Watched Purchase Pools
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <ul className="list-disc pl-4">
                                    {watchedPurchasePools.map((pool) => (
                                        <li key={pool.id} className="text-sm">
                                            {pool.name}
                                        </li>
                                    ))}
                                </ul>
                            </CardContent>
                        </Card>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
