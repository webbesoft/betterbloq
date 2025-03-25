import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Product } from '@/types/model-types';
import { Head } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Market',
        href: '/market',
    },
];

interface ProductData {
    data: Product[];
}

interface MarketProps {
    products: ProductData;
}

export default function Market(props: MarketProps) {
    console.log(props);
    const { products } = props;

    const { data } = products;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Market" />
            <div className="h-full rounded-xl p-4">
                <div className="grid grid-cols-4">
                    {data.map((product: Product) => {
                        return (
                            <div className="flex flex-col gap-2 rounded-md border-2 border-black p-4" key={product.id}>
                                <img src={product.image} alt={product.name} />
                                <p>{product.name}</p>
                                <a className="button primary-button text-center" href={'/market/product/' + product.id}>
                                    Details
                                </a>
                            </div>
                        );
                    })}
                </div>
            </div>
        </AppLayout>
    );
}
