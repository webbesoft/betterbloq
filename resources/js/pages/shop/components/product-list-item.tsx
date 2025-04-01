import { Product } from '@/types/model-types';

interface ProductListItemProps {
    product: Product;
}

export default function ProductListItem(props: ProductListItemProps) {
    const { product } = props;

    return (
        <a
            className="relative bg-[var(--background)] text-center opacity-90 transition-all hover:scale-[101%] hover:shadow-md"
            href={'/market/product/' + product.id}
        >
            <div className="absolute inset-0 rounded-sm bg-black opacity-[3%]"></div> {/* Adjust opacity as needed */}
            <div className="flex flex-col gap-4 rounded-sm p-4" key={product.id}>
                <p className="text text-lg font-bold">{product.vendor.name}</p>
                <div className="aspect-w-1 aspect-h-1">
                    <img src={product.image} alt={product.name} className="rounded-sm object-cover" />
                </div>
                <p>{product.name}</p>
                <p className="text text-lg font-semibold">${product.price}</p>
            </div>
        </a>
    );
}
