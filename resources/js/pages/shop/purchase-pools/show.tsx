import {
    Card,
    CardHeader,
    CardTitle,
    CardContent,
} from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Link, Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import {
    Table,
    TableHeader,
    TableBody,
    TableRow,
    TableHead,
    TableCell,
} from "@/components/ui/table";
import { PurchasePool } from '@/types/model-types';

interface Props {
    purchasePool: PurchasePool & {
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
}

const PurchasePoolShow: React.FC<Props> = ({ purchasePool }) => {
    return (
        <AppLayout>
            <Head title={purchasePool.name} />
            <div className="space-y-4">
                <div className="flex justify-between items-center mb-4">
                    <h2 className="text-2xl font-bold">{purchasePool.name}</h2>
                    <div>
                        <Button variant="outline" size="sm" className="mr-2">
                            Exit Purchase Pool {/* Implement the logic for this button */}
                        </Button>
                        <Link href={route('purchase-pools.index')}>
                            <Button size="sm">Back to Pools</Button>
                        </Link>
                    </div>
                </div>

                {purchasePool.description && (
                    <Card className="mb-4">
                        <CardHeader>
                            <CardTitle>Description</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p>{purchasePool.description}</p>
                        </CardContent>
                    </Card>
                )}

                <Card>
                    <CardHeader>
                        <CardTitle>Purchase Pool Tiers</CardTitle>
                    </CardHeader>
                    <CardContent className="p-0">
                        <div className="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Order</TableHead>
                                        <TableHead>Name</TableHead>
                                        <TableHead>Description</TableHead>
                                        <TableHead>Min Volume</TableHead>
                                        <TableHead>Max Volume</TableHead>
                                        <TableHead className="text-right">Discount (%)</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {purchasePool.tiers.sort((a, b) => a.order - b.order).map((tier) => (
                                        <TableRow key={tier.id}>
                                            <TableCell>{tier.order}</TableCell>
                                            <TableCell>{tier.name}</TableCell>
                                            <TableCell>{tier.description}</TableCell>
                                            <TableCell>{tier.min_volume !== null ? tier.min_volume : 'N/A'}</TableCell>
                                            <TableCell>{tier.max_volume !== null ? tier.max_volume : 'N/A'}</TableCell>
                                            <TableCell className="text-right">{tier.discount_percentage !== null ? tier.discount_percentage : 'N/A'}</TableCell>
                                        </TableRow>
                                    ))}
                                    {purchasePool.tiers.length === 0 && (
                                        <TableRow>
                                            <TableCell colSpan={6} className="text-center py-4">
                                                No tiers defined for this purchase pool.
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
};

export default PurchasePoolShow;
