import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { Users } from 'lucide-react'; // Changed Icon
import { Link } from '@inertiajs/react';

interface PoolProgressData {
    id: number;
    name: string;
    product_id: number | null;
    current_volume: number;
    target_volume: number;
}

interface ActivePoolsProgressProps {
    pools: PoolProgressData[];
}

export default function ActivePoolsProgress({ pools }: ActivePoolsProgressProps) {
    if (!pools || pools.length === 0) {
        return (
             <Card>
                 <CardHeader>
                     <CardTitle className="flex items-center gap-2 text-base font-semibold"> {/* Adjusted title style */}
                         <Users className="h-4 w-4" /> Active Purchase Pool Progress
                     </CardTitle>
                 </CardHeader>
                 <CardContent>
                     <p className="text-muted-foreground text-sm">You have no active orders in purchase pools.</p>
                 </CardContent>
             </Card>
        );
    }

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center gap-2 text-base font-semibold"> {/* Adjusted title style */}
                    <Users className="h-4 w-4" /> Active Purchase Pool Progress
                </CardTitle>
            </CardHeader>
            <CardContent className="flex flex-col gap-4">
                {pools.map((pool) => {
                    const current = pool.current_volume;
                    const target = pool.target_volume;
                    const progress = target > 0 ? Math.min(100, Math.round((current / target) * 100)) : 0;
    const formatVolume = (value: number) => value.toLocaleString();
                    return (
                        <div key={pool.id}>
                            <div className="mb-1 flex flex-wrap justify-between items-center gap-x-2 gap-y-1"> {/* Added flex-wrap and gap */}
                                {pool.product_id ? (
                                     <Link href={`/market/product/${pool.product_id}`} className="text-sm font-medium hover:underline truncate" title={pool.name}>
                                        {pool.name}
                                    </Link>
                                ) : (
                                     <span className="text-sm font-medium truncate" title={pool.name}>{pool.name}</span>
                                )}

                                <span className="text-xs text-muted-foreground whitespace-nowrap">
                                    {formatVolume(current)} / {formatVolume(target)} ({progress}%)
                                </span>
                            </div>
                            <Progress value={progress} aria-label={`${pool.name} progress`} />
                        </div>
                    );
                })}
            </CardContent>
        </Card>
    );
}