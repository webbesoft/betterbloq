// components/ProductListItemListLayout.jsx
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Product } from '@/types/model-types';
import { Star, Heart, Truck } from 'lucide-react';

interface ProductListItemProps {
    product: Product;
}

// Define a Skeleton component for loading states (Optional but recommended)
export function ProductListItemSkeleton() {
    return (
        <div className="flex flex-col gap-4 rounded-lg border bg-card p-4 sm:flex-row sm:gap-6">
            {/* Skeleton for Image */}
            <div className="flex-shrink-0 sm:w-32 md:w-40">
                <div className="aspect-square w-full animate-pulse rounded-md bg-muted"></div>
            </div>
            {/* Skeleton for Details */}
            <div className="flex-1 space-y-3 py-1">
                <div className="h-4 w-1/4 animate-pulse rounded bg-muted"></div> {/* Vendor */}
                <div className="h-5 w-3/4 animate-pulse rounded bg-muted"></div> {/* Title */}
                <div className="h-4 w-1/2 animate-pulse rounded bg-muted"></div> {/* Rating */}
                <div className="h-4 w-1/3 animate-pulse rounded bg-muted"></div> {/* Stock */}
            </div>
            {/* Skeleton for Price/Actions */}
            <div className="flex flex-col items-start pt-2 sm:w-32 sm:items-end sm:pt-0 md:w-40">
                <div className="mb-2 h-6 w-1/2 animate-pulse rounded bg-muted"></div> {/* Price */}
                <div className="h-9 w-full animate-pulse rounded bg-muted sm:w-24"></div> {/* Button */}
            </div>
        </div>
    )
}


export default function ProductListItemListLayout({ product }: ProductListItemProps) {
    const averageRating = product.average_rating || 0;
    const reviewCount = product.review_count || 0;

    return (
        // Row container: Flex column on mobile, row on larger screens. Card styling.
        <div className="flex flex-col gap-4 rounded-lg border bg-card p-4 text-card-foreground shadow-sm transition-all hover:shadow-md sm:flex-row sm:gap-6">

            {/* --- Image Section (Left) --- */}
            <div className="mx-auto w-40 flex-shrink-0 sm:mx-0 sm:w-32 md:w-40"> {/* Centered on mobile */}
                <a href={'/market/product/' + product.id} className="block aspect-square overflow-hidden rounded-md bg-gray-100"> {/* Ensure aspect ratio */}
                    <img
                        src={product.image}
                        alt={product.name}
                        className="h-full w-full object-cover transition-transform duration-300 ease-in-out hover:scale-105"
                        loading="lazy" // Add lazy loading for performance
                    />
                </a>
            </div>

            {/* --- Details Section (Middle) --- */}
            <div className="flex flex-1 flex-col justify-center"> {/* Centered vertically */}
                {/* Vendor (Optional) */}
                <p className="mb-1 text-xs text-muted-foreground">{product.vendor.name}</p>

                {/* Product Name */}
                <a href={'/market/product/' + product.id} className="block">
                    <h3 className="mb-1 line-clamp-2 text-base font-semibold leading-tight hover:text-primary sm:text-lg">
                        {product.name}
                    </h3>
                </a>

                {/* Ratings */}
                {reviewCount > 0 ? (
                    <a href={'/market/product/' + product.id + '#reviews'} className="mb-2 flex items-center gap-1" title={`${averageRating.toFixed(1)} out of 5 stars`}>
                        <div className="flex items-center text-yellow-500">
                            {[...Array(5)].map((_, i) => (
                                <Star key={i} size={16} className={i < Math.round(averageRating) ? 'fill-current' : 'stroke-current'} />
                            ))}
                        </div>
                        <span className="text-xs text-muted-foreground hover:underline">({reviewCount})</span>
                    </a>
                ) : (
                    <div className="mb-2 h-5"></div> // Placeholder for spacing if no reviews
                )}

                {/* Stock/Delivery Info */}
                <div className="mb-2 flex items-center gap-2 text-sm text-green-600">
                    <Truck size={16} />
                    {/* Replace with actual stock logic */}
                    <span>In Stock</span>
                    {/* Example: <span className="text-orange-600">Low Stock</span> */}
                    {/* Example: <span className="text-gray-500">Ships in 2-3 days</span> */}
                </div>

                {/* Badges */}
                 <div className="mt-1 flex flex-wrap gap-2">
                     {product.is_on_sale && <Badge variant="destructive">Sale</Badge>}
                     {product.is_new && <Badge>New</Badge>}
                     {/* Add more relevant badges like 'Free Shipping', 'Top Rated', etc. */}
                 </div>
            </div>

             {/* --- Price & Actions Section (Right) --- */}
             <div className="flex flex-col items-start justify-center pt-2 sm:w-32 sm:items-end sm:pt-0 md:w-40">
                 {/* Price */}
                 <div className="mb-2">
                     <p className="text-lg font-semibold sm:text-xl">
                         ${product.price}
                     </p>
                     {product.is_on_sale && product.original_price && (
                         <p className="text-sm text-muted-foreground line-through">
                            ${product.original_price}
                        </p>
                    )}
                 </div>

                 {/* Actions */}
                 <div className="flex w-full flex-col gap-2 sm:items-end">
                    <a href={'/market/product/' + product.id}>
                        <Button variant="outline" size="sm" className="mt-1">
                            View
                        </Button>
                    </a>
                    <Button variant="ghost" size="sm" className="hidden items-center gap-1 text-muted-foreground hover:text-primary sm:inline-flex" title="Add to Wishlist">
                         <Heart size={14} />
                         <span className="text-xs">Save</span>
                    </Button>
                </div>
            </div>

        </div>
    );
}