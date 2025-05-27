import { Card, CardContent } from '@/components/ui/card';
import LandingLayout from '@/layouts/landing-layout';
import { Head, Link } from '@inertiajs/react';
// import Pagination from '@/components/pagination';

interface CategoryItem {
    id: number;
    name: string;
    image_url: string | null;
    products_count?: number;
}

interface CategoryProps {
    categories: {
        data: CategoryItem[];
        links: any[];
    };
}

const breadcrumbs = [
    { title: 'Home', href: route('landing') },
    { title: 'Categories', href: route('categories.index') },
];

export default function CategoryIndex({ categories }: CategoryProps) {
    const placeholderImage = '/images/category_placeholder.png';

    return (
        <LandingLayout breadcrumbs={breadcrumbs}>
            <Head title="Product Categories" />

            <div className="container mx-auto py-8">
                <h1 className="mb-6 text-3xl font-bold tracking-tight">Product Categories</h1>

                {categories.data.length > 0 ? (
                    <>
                        <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
                            {categories.data.map((category) => (
                                <Link
                                    key={category.id}
                                    href={route('market', { category: category.id })}
                                    className="group block transition-transform duration-200 hover:scale-105"
                                >
                                    <Card className="overflow-hidden shadow-sm transition-shadow duration-200 group-hover:shadow-md">
                                        <CardContent className="p-0">
                                            <div className="bg-muted aspect-square w-full overflow-hidden">
                                                <img
                                                    src={category.image_url ?? placeholderImage}
                                                    alt={category.name}
                                                    className="h-full w-full object-cover transition-opacity duration-200 group-hover:opacity-90"
                                                    onError={(e) => {
                                                        (e.target as HTMLImageElement).src =
                                                            'https://placehold.co/600x400/e2e8f0/cbd5e0?text=Image+Not+Available';
                                                    }}
                                                />
                                            </div>
                                            <div className="p-3 text-center">
                                                <h3 className="group-hover:text-primary truncate text-sm font-medium">{category.name}</h3>
                                                {category.products_count !== undefined && (
                                                    <p className="text-muted-foreground text-xs">{category.products_count} products</p>
                                                )}
                                            </div>
                                        </CardContent>
                                    </Card>
                                </Link>
                            ))}
                        </div>

                        {/* Pagination Links */}
                        {/* <div className="mt-8">
                            <Pagination links={categories.links} />
                        </div> */}
                    </>
                ) : (
                    <p className="text-muted-foreground">No categories with products found.</p>
                )}
            </div>
        </LandingLayout>
    );
}
