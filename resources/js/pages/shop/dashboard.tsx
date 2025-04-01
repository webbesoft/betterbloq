import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Eye, ListChecks, MapPin, PieChart, ShoppingBag } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'BetterBuy Dashboard',
        href: '/dashboard',
    },
];

export default function Dashboard({
    ongoingProjectsCount,
    totalExpenses,
    projectBudgetSpent,
    purchasePoolCompletion,
    watchedPurchasePools,
    frequentProducts,
    regularVendors,
}) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="grid auto-rows-min gap-4 md:grid-cols-2 lg:grid-cols-3">
                    {/* Ongoing Projects Overview */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <ListChecks className="h-4 w-4" /> Ongoing Projects
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            {ongoingProjectsCount !== null ? (
                                <p className="text-2xl font-bold">{ongoingProjectsCount}</p>
                            ) : (
                                <PlaceholderPattern className="h-6 w-12" />
                            )}
                        </CardContent>
                    </Card>

                    {/* Total Expenses Overview */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <ShoppingBag className="h-4 w-4" /> Total Expenses
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            {totalExpenses !== null ? (
                                <p className="text-2xl font-bold">${totalExpenses?.toLocaleString()}</p>
                            ) : (
                                <PlaceholderPattern className="h-6 w-24" />
                            )}
                        </CardContent>
                    </Card>

                    {/* Placeholder for another key metric */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Key Metric 3</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <PlaceholderPattern className="h-10 w-full" />
                        </CardContent>
                    </Card>
                </div>

                <div className="grid auto-rows-min gap-4 md:grid-cols-1 lg:grid-cols-2">
                    {/* Project Budget Spent Chart */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <PieChart className="h-4 w-4" /> Project Budget Overview
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            {projectBudgetSpent.length > 0 ? (
                                // You'll integrate a charting library here (e.g., Recharts, Chart.js)
                                <div className="flex h-64 w-full items-center justify-center">
                                    {/* Placeholder for your chart */}
                                    <PlaceholderPattern className="h-48 w-48" />
                                </div>
                            ) : (
                                <p className="text-muted-foreground text-sm">No project budget data available.</p>
                            )}
                        </CardContent>
                    </Card>

                    {/* Purchase Pool Completion Rundown */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Purchase Pool Completion Status</CardTitle>
                        </CardHeader>
                        <CardContent>
                            {purchasePoolCompletion.length > 0 ? (
                                <div className="space-y-4">
                                    {purchasePoolCompletion.map((pool) => (
                                        <div key={pool.id}>
                                            <h3 className="text-sm font-semibold">{pool.name}</h3>
                                            <div className="relative h-4 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                                <div
                                                    className="bg-primary absolute top-0 left-0 h-full rounded-full"
                                                    style={{ width: `${(pool.current_amount / pool.target_amount) * 100}%` }}
                                                ></div>
                                                <span className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-xs font-semibold text-gray-700 dark:text-gray-300">
                                                    {((pool.current_amount / pool.target_amount) * 100).toFixed(0)}%
                                                </span>
                                            </div>
                                            <p className="text-muted-foreground mt-1 text-xs">
                                                ${pool.current_amount.toLocaleString()} / ${pool.target_amount.toLocaleString()}
                                            </p>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-muted-foreground text-sm">No purchase pool completion data.</p>
                            )}
                        </CardContent>
                    </Card>
                </div>

                <div className="grid auto-rows-min gap-4 md:grid-cols-1 lg:grid-cols-1">
                    {/* Project Location Map (Placeholder) */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <MapPin className="h-4 w-4" /> Project Locations
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="h-[400px]">
                            <div className="flex h-full w-full items-center justify-center rounded-md bg-gray-100 dark:bg-gray-800">
                                <PlaceholderPattern className="h-32 w-32" />
                                <p className="text-muted-foreground text-lg">Map Placeholder</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid auto-rows-min gap-4 md:grid-cols-1 lg:grid-cols-2">
                    {/* Frequently Bought Products */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Frequently Bought Products</CardTitle>
                        </CardHeader>
                        <CardContent>
                            {frequentProducts.length > 0 ? (
                                <ol className="list-decimal pl-4">
                                    {frequentProducts.map((product) => (
                                        <li key={product.product_id} className="text-sm">
                                            {product.name} (Bought {product.frequency} times)
                                        </li>
                                    ))}
                                </ol>
                            ) : (
                                <p className="text-muted-foreground text-sm">No frequent purchase history yet.</p>
                            )}
                        </CardContent>
                    </Card>

                    {/* Regular Vendors */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Regular Vendors</CardTitle>
                        </CardHeader>
                        <CardContent>
                            {regularVendors.length > 0 ? (
                                <ol className="list-decimal pl-4">
                                    {regularVendors.map((vendor) => (
                                        <li key={vendor.vendor_id} className="text-sm">
                                            {vendor.name} (Bought {vendor.frequency} times)
                                        </li>
                                    ))}
                                </ol>
                            ) : (
                                <p className="text-muted-foreground text-sm">No regular vendors yet.</p>
                            )}
                        </CardContent>
                    </Card>
                </div>

                {watchedPurchasePools.length > 0 && (
                    <div className="grid auto-rows-min gap-4 md:grid-cols-1 lg:grid-cols-1">
                        {/* Watched Purchase Pools */}
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
