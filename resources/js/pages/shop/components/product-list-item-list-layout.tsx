import { Button } from '@/components/ui/button';
import { Product } from '@/types/model-types';
import { Link } from '@inertiajs/react';
import { ArrowUpRight, Package, Star, Truck } from 'lucide-react';
import { useState } from 'react';

export function ProductListItemSkeleton() {
    return (
        <div className="surface overflow-hidden" aria-label="Loading material">
            <div className="bg-muted aspect-[4/3] animate-pulse" />
            <div className="space-y-3 p-5">
                <div className="bg-muted h-3 w-1/3 animate-pulse rounded" />
                <div className="bg-muted h-5 w-2/3 animate-pulse rounded" />
                <div className="bg-muted mt-6 h-9 animate-pulse rounded" />
            </div>
        </div>
    );
}

export default function ProductListItemListLayout({ product }: { product: Product }) {
    const [imageFailed, setImageFailed] = useState(false);
    const href = route('product.show', product.id);
    const reviewCount = Number(product.ratings_count) || 0;
    const rating = Number(product.average_rating) || 0;

    return (
        <article className="surface group hover:border-primary/30 flex h-full flex-col overflow-hidden transition-colors duration-200">
            <Link
                href={href}
                aria-label={`View ${product.name}`}
                className="bg-muted/60 relative flex aspect-[4/3] items-center justify-center overflow-hidden border-b"
            >
                {product.image && !imageFailed ? (
                    <img
                        src={product.image}
                        alt={product.name}
                        className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                        loading="lazy"
                        onError={() => setImageFailed(true)}
                    />
                ) : (
                    <div className="text-muted-foreground/50 flex flex-col items-center gap-3">
                        <Package className="size-14 stroke-1" />
                        <span className="text-[10px] tracking-[0.16em] uppercase">Material preview</span>
                    </div>
                )}
                {product.category && (
                    <span className="bg-card/95 text-foreground absolute top-3 left-3 max-w-[calc(100%-1.5rem)] truncate rounded-md px-2.5 py-1 text-[10px] font-medium backdrop-blur-sm">
                        {product.category}
                    </span>
                )}
            </Link>
            <div className="flex flex-1 flex-col p-5">
                <p className="text-muted-foreground text-[11px]">{product.vendor.name}</p>
                <Link href={href} className="mt-1.5">
                    <h2 className="group-hover:text-primary line-clamp-2 text-base leading-6 font-semibold tracking-tight transition-colors">
                        {product.name}
                    </h2>
                </Link>
                <div className="text-muted-foreground mt-3 flex min-h-5 items-center gap-2 text-[11px]">
                    {reviewCount > 0 ? (
                        <>
                            <Star className="size-3.5 fill-[#b88a40] text-[#b88a40]" />
                            <span className="text-foreground font-medium">{rating.toFixed(1)}</span>
                            <span>
                                ({reviewCount} {reviewCount === 1 ? 'review' : 'reviews'})
                            </span>
                        </>
                    ) : (
                        <>
                            <Package className="size-3.5" />
                            <span>Explore buying options</span>
                        </>
                    )}
                </div>
                <div className="mt-auto pt-5">
                    <div className="flex items-baseline gap-1.5">
                        <span className="text-2xl font-semibold tracking-tight tabular-nums">${Number(product.price).toFixed(2)}</span>
                        <span className="text-muted-foreground text-xs">/ {product.unit}</span>
                    </div>
                    {Number(product.delivery_time) > 0 && (
                        <p className="text-muted-foreground mt-1.5 flex items-center gap-1.5 text-[10px]">
                            <Truck className="size-3" />
                            Product lead time: {product.delivery_time} days
                        </p>
                    )}
                    <Button
                        asChild
                        variant="outline"
                        className="group-hover:border-primary/40 group-hover:text-primary mt-4 h-10 w-full justify-between rounded-sm text-xs"
                    >
                        <Link href={href}>
                            View material
                            <ArrowUpRight className="size-3.5" />
                        </Link>
                    </Button>
                </div>
            </div>
        </article>
    );
}
