import EmptyResource from '@/components/empty-resource';
import { Pagination, PaginationContent, PaginationItem, PaginationLink, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { PaginationBaseLinks, PaginationType, Product } from '@/types/model-types';
import { Head } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Market',
        href: '/market',
    },
];

interface ProductData {
    data: Product[];
    meta: PaginationType;
    links: PaginationBaseLinks;
}

interface MarketProps {
    products: ProductData;
}

export default function Market(props: MarketProps) {
    const { products } = props;

    const { data, meta, links } = products;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Market" />
            <div className="h-full rounded-xl p-4">
                <div className="grid grid-cols-4 gap-4">
                    {data.length === 0 && (
                        <EmptyResource type="products" />
                    )}
                    {data.map((product: Product) => {
                        return (
                            <div className="flex flex-col gap-2 rounded-md shadow-xs p-4" key={product.id}>
                                <img src={product.image} alt={product.name} />
                                <p>{product.name}</p>
                                <p>{product.category}</p>
                                <p>Vendor: {product.vendor.name}</p>
                                <p>${product.price}</p>
                                <a className="button primary-button text-center" href={'/market/product/' + product.id}>
                                    Details
                                </a>
                            </div>
                        );
                    })}
                </div>
                <div className='mt-2'>
                    {
                        meta && meta.total > 1 && (
                            <Pagination>
                                <PaginationContent className='gap-2'>
                                    <PaginationItem>
                                        <PaginationPrevious hidden={! links.prev} href={links.prev} size={1} />
                                    </PaginationItem>
                                    <PaginationItem className='shadow-sm px-2'>
                                        <PaginationLink href="#" size={1}>1</PaginationLink>
                                    </PaginationItem>
                                    <PaginationItem>
                                        {/* <PaginationEllipsis /> */}
                                    </PaginationItem>
                                    <PaginationItem>
                                        <PaginationNext hidden={! links.next} href={links.next} size={1} />
                                    </PaginationItem>
                                </PaginationContent>
                            </Pagination>
                        )
                    }
                </div>
            </div>
        </AppLayout>
    );
}
