import { Button } from '@/components/ui/button';
import { Product } from '@/types/model-types';
import { Badge, Star } from 'lucide-react';

interface ProductListItemProps {
    product: Product;
}

export default function ProductListItem(props: ProductListItemProps) {
    const { product } = props;
    const averageRating = product.average_rating || 0; // Assuming you might add this data
    const reviewCount = product.review_count || 0;

    return (
        // Use Card component for better structure and styling options
        <div className="relative flex h-full flex-col overflow-hidden rounded-md border bg-card text-card-foreground shadow-sm transition-all hover:shadow-lg">
            <a href={'/market/product/' + product.id} className="block">
                <div className="aspect-h-1 aspect-w-1 overflow-hidden bg-gray-100">
                    <img
                        src={product.image}
                        alt={product.name}
                        className="h-full w-full object-cover transition-transform duration-300 ease-in-out group-hover:scale-105" // Add hover effect if inside group
                    />
                </div>
            </a>

            {/* Optional: Badges like Sale, New, etc. */}
            {product.is_on_sale && (
                 <Badge variant="destructive" className="absolute left-2 top-2">Sale</Badge>
            )}
             {product.is_new && (
                 <Badge className="absolute right-2 top-2">New</Badge>
            )}


            <div className="flex flex-1 flex-col p-4"> {/* Use padding here */}
                {/* Vendor (Optional - might be redundant if filtering by vendor) */}
                {/* <p className="text-xs text-muted-foreground mb-1">{product.vendor.name}</p> */}

                {/* Product Name */}
                <a href={'/market/product/' + product.id} className="block">
                    <h3 className="mb-2 line-clamp-2 font-semibold leading-tight hover:text-primary">
                        {product.name}
                    </h3>
                </a>

                {/* Ratings */}
                {reviewCount > 0 && (
                    <div className="mb-2 flex items-center gap-1">
                        <div className="flex items-center text-yellow-500">
                            {[...Array(5)].map((_, i) => (
                                <Star key={i} size={16} fill={i < Math.round(averageRating) ? 'currentColor' : 'none'} />
                            ))}
                        </div>
                        <span className="text-xs text-muted-foreground">({reviewCount})</span>
                    </div>
                )}

                 {/* Spacer to push price/button down */}
                 <div className="flex-grow"></div>

                {/* Price */}
                <p className="mb-3 text-lg font-semibold">
                    ${product.price}
                    {/* Optional: Show original price if on sale */}
                    {product.is_on_sale && product.original_price && (
                        <span className="ml-2 text-sm text-muted-foreground line-through">${product.original_price}</span>
                    )}
                </p>

                {/* Actions */}
                {/* <Button
                    variant="outline"
                    size="sm"
                    onClick={(e) => {
                        e.preventDefault(); // Prevent link navigation if clicking button
                        // Add to cart logic here
                        console.log('Add to cart:', product.id);
                    }}
                >
                    Add to Cart
                </Button> */}
                {/* Optional: Quick View Button */}
                <a href={'/market/product/' + product.id}>
                    <Button variant="outline" size="sm" className="mt-1">
                        View
                    </Button>
                </a>
            </div>
        </div>
    );
}
