// components/ProductListItemListLayout.jsx
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Product } from '@/types/model-types';
import { Star, Truck } from 'lucide-react';

interface ProductListItemProps {
    product: Product;
}

// Define a Skeleton component for loading states (Optional but recommended)
export function ProductListItemSkeleton() {
    return (
        <div className="bg-card flex flex-col gap-4 rounded-lg border p-4 sm:flex-row sm:gap-6">
            {/* Skeleton for Image */}
            <div className="flex-shrink-0 sm:w-32 md:w-40">
                <div className="bg-muted aspect-square w-full animate-pulse rounded-md"></div>
            </div>
            {/* Skeleton for Details */}
            <div className="flex-1 space-y-3 py-1">
                <div className="bg-muted h-4 w-1/4 animate-pulse rounded"></div> {/* Vendor */}
                <div className="bg-muted h-5 w-3/4 animate-pulse rounded"></div> {/* Title */}
                <div className="bg-muted h-4 w-1/2 animate-pulse rounded"></div> {/* Rating */}
                <div className="bg-muted h-4 w-1/3 animate-pulse rounded"></div> {/* Stock */}
            </div>
            {/* Skeleton for Price/Actions */}
            <div className="flex flex-col items-start pt-2 sm:w-32 sm:items-end sm:pt-0 md:w-40">
                <div className="bg-muted mb-2 h-6 w-1/2 animate-pulse rounded"></div> {/* Price */}
                <div className="bg-muted h-9 w-full animate-pulse rounded sm:w-24"></div> {/* Button */}
            </div>
        </div>
    );
}

export default function ProductListItemListLayout({ product }: ProductListItemProps) {
    const averageRating = product.average_rating || 0;
    const reviewCount = product.ratings_count || 0;

    return (
        <div className="bg-card text-card-foreground flex flex-col gap-4 rounded-lg border p-4 shadow-sm transition-all hover:shadow-md sm:flex-row sm:gap-6">
            <div className="mx-auto w-40 flex-shrink-0 sm:mx-0 sm:w-32 md:w-40">
                {' '}
                <a href={'/market/product/' + product.id} className="block aspect-square overflow-hidden rounded-md bg-gray-100">
                    {' '}
                    <img
                        src={product.image}
                        alt={product.name}
                        className="h-full w-full object-cover transition-transform duration-300 ease-in-out hover:scale-105"
                        loading="lazy"
                        onError={(e) => {
                            (e.target as HTMLImageElement).src = 'https://placehold.co/600x400/e2e8f0/cbd5e0?text=Image+Not+Available';
                        }}
                    />
                </a>
            </div>

            <div className="flex flex-1 flex-col justify-center">
                {' '}
                <p className="text-muted-foreground mb-1 text-xs">{product.vendor.name}</p>
                <a href={'/market/product/' + product.id} className="block">
                    <h3 className="hover:text-primary mb-1 line-clamp-2 text-base leading-tight font-semibold sm:text-lg">{product.name}</h3>
                </a>
                {/* Ratings */}
                {reviewCount > 0 ? (
                    <a
                        href={'/market/product/' + product.id + '#reviews'}
                        className="mb-2 flex items-center gap-1"
                        title={`${averageRating.toFixed(1)} out of 5 stars`}
                    >
                        <div className="flex items-center text-yellow-500">
                            {[...Array(5)].map((_, i) => (
                                <Star key={i} size={16} className={i < Math.round(averageRating) ? 'fill-current' : 'stroke-current'} />
                            ))}
                        </div>
                        <span className="text-muted-foreground text-xs hover:underline">({reviewCount})</span>
                    </a>
                ) : (
                    <div className="mb-2 h-5"></div>
                )}
                <div className="mb-2 flex items-center gap-2 text-sm text-green-600">
                    <Truck size={16} />

                    <span>In Stock</span>
                    {/* Example: <span className="text-orange-600">Low Stock</span> */}
                    {/* Example: <span className="text-gray-500">Ships in 2-3 days</span> */}
                </div>
                {/* Badges */}
                <div className="mt-1 flex flex-wrap gap-2">
                    {product.is_on_sale && <Badge variant="destructive">Sale</Badge>}
                    {product.is_new && <Badge>New</Badge>}
                </div>
            </div>

            {/* --- Price & Actions Section (Right) --- */}
            <div className="flex flex-col items-start justify-center pt-2 sm:w-32 sm:items-end sm:pt-0 md:w-40">
                {/* Price */}
                <div className="mb-2">
                    <p className="text-lg font-semibold sm:text-xl">${Number(product.price).toFixed(2)}</p>
                    {product.is_on_sale && product.original_price && (
                        <p className="text-muted-foreground text-sm line-through">${product.original_price}</p>
                    )}
                </div>

                {/* Actions */}
                <div className="flex w-full flex-col gap-2 sm:items-end">
                    <a href={'/market/product/' + product.id}>
                        <Button variant="outline" size="sm" className="mt-1">
                            View
                        </Button>
                    </a>
                    {/* <Button
                        variant="ghost"
                        size="sm"
                        className="text-muted-foreground hover:text-primary hidden items-center gap-1 sm:inline-flex"
                        title="Add to Wishlist"
                    >
                        <ShoppingCart size={14} />
                        <span className="text-xs">Add to cart</span>
                    </Button> */}
                </div>
            </div>
        </div>
    );
}
