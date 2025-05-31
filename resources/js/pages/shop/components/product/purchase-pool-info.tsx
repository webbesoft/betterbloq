import { Badge } from '@/components/ui/badge';
import { formatDate } from '@/lib/helpers';
import { Product } from '@/types/model-types';
import { Progress } from '@radix-ui/react-progress';
import { Calendar, CheckCircle, Info, Tag, Users } from 'lucide-react';
import { ActivePurchasePoolData } from '../../product';
import { CountdownTimer } from '../countdown-timer';

interface PurchasePoolInfoProps {
    activePurchasePool: ActivePurchasePoolData;
    product: Product;
}

export const PurchasePoolInfo = (props: PurchasePoolInfoProps) => {
    const { activePurchasePool, product } = props;

    if (!activePurchasePool) {
        return (
            <div className="border-border mt-6 rounded-md border border-dashed p-6 text-center">
                <Info className="text-muted-foreground mx-auto mb-3 h-8 w-8" />
                <p className="text-muted-foreground text-base">No active purchase pool currently available for this product.</p>
            </div>
        );
    }

    const { current_volume, tiers, current_tier, end_date, target_delivery_date, target_volume } = activePurchasePool;
    const progressPercent = target_volume > 0 ? Math.min(100, (current_volume / target_volume) * 100) : 0;
    const nextTier = tiers.find((tier) => tier.min_volume > current_volume && tier.id !== current_tier?.id);

    return (
        <div className="from-background to-secondary/20 mt-6 space-y-6 rounded-lg border bg-gradient-to-br p-6 shadow-sm">
            <div className="flex flex-col items-center gap-2 sm:flex-row sm:justify-between">
                <h2 className="flex items-center gap-3 text-xl font-semibold lg:text-2xl">
                    <Users className="text-primary h-6 w-6" /> Active Purchase Pool
                </h2>
                <Badge variant="default" className="text-sm">
                    Active
                </Badge>
            </div>

            {end_date && (
                <div className="bg-primary/10 text-primary-foreground rounded-md p-4 text-center">
                    <p className="text-primary mb-1 text-sm font-medium">Current purchase cycle ends in:</p>
                    <CountdownTimer endDate={end_date} />
                </div>
            )}

            {target_volume > 0 && (
                <div className="space-y-2">
                    <div className="text-muted-foreground mb-1 flex justify-between text-sm font-medium">
                        <span>
                            Current Volume: {current_volume} / {target_volume} {product.unit}s
                        </span>
                        {nextTier && (
                            <span>
                                Next Tier at {nextTier.min_volume} {product.unit}s
                            </span>
                        )}
                    </div>
                    <Progress value={progressPercent} className="h-3 w-full" />
                    {nextTier && (
                        <p className="text-muted-foreground mt-1 text-right text-xs">
                            {Math.max(0, nextTier.min_volume - current_volume)} more {product.unit}s needed for {nextTier.discount_percentage}%
                            discount
                        </p>
                    )}
                    {current_tier && !nextTier && progressPercent === 100 && (
                        <p className="text-primary mt-1 text-right text-sm font-semibold">Target volume reached! Discount locked in.</p>
                    )}
                </div>
            )}
            {!target_volume && (
                <p className="text-muted-foreground text-sm">
                    Current Volume: {current_volume} {product.unit}s
                </p>
            )}

            <div className="space-y-3">
                <p className="text-foreground text-md font-semibold">Discount Tiers:</p>
                <ul className="list-none space-y-2">
                    {tiers.map((tier) => (
                        <li
                            key={tier.id}
                            className={`flex items-start gap-3 rounded-md border p-3 transition-all ${current_tier?.id === tier.id ? 'border-primary bg-primary/5 shadow-md' : 'border-border hover:bg-muted/50'}`}
                        >
                            {current_tier?.id === tier.id ? (
                                <CheckCircle className="text-primary mt-0.5 h-5 w-5 flex-shrink-0" />
                            ) : (
                                <Tag className="text-muted-foreground mt-0.5 h-5 w-5 flex-shrink-0" />
                            )}
                            <div className="flex-grow">
                                <span className={`font-medium ${current_tier?.id === tier.id ? 'text-primary' : 'text-foreground'}`}>
                                    {tier.discount_percentage}% off
                                </span>
                                <span className="text-muted-foreground block text-sm">
                                    at {Number(tier.min_volume).toFixed(0)}
                                    {tier.max_volume ? ` - ${Number(tier.max_volume).toFixed(0)} ${product.unit}s` : `+ ${product.unit}s`}
                                </span>
                                {tier.description && <p className="text-muted-foreground/80 mt-1 text-xs">{tier.description}</p>}
                            </div>
                            {current_tier?.id === tier.id && (
                                <Badge variant="outline" className="border-primary text-primary ml-auto self-center whitespace-nowrap">
                                    Current Tier
                                </Badge>
                            )}
                        </li>
                    ))}
                </ul>
            </div>

            <div className="grid grid-cols-1 gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                {target_delivery_date && (
                    <div className="text-muted-foreground flex items-center gap-2">
                        <Calendar className="h-4 w-4 flex-shrink-0" />
                        <div>
                            <span>Target Delivery: </span>
                            <span className="text-foreground font-medium">{formatDate(target_delivery_date)}</span>
                        </div>
                    </div>
                )}
                {activePurchasePool.min_orders_for_discount > 0 && (
                    <div className="text-muted-foreground flex items-center gap-2">
                        <Users className="h-4 w-4 flex-shrink-0" />
                        <div>
                            <span>Min Orders for Discount: </span>
                            <span className="text-foreground font-medium">{activePurchasePool.min_orders_for_discount}</span>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
};
