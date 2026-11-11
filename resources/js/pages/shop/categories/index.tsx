import { Button } from '@/components/ui/button';
import LandingLayout from '@/layouts/landing-layout';
import { Head, Link } from '@inertiajs/react';
import { ArrowRight, ArrowUpRight, Boxes, Layers3 } from 'lucide-react';
import { useState } from 'react';

interface CategoryItem {
    id: number;
    name: string;
    image_url: string | null;
    products_count?: number;
}
interface CategoryProps {
    categories: { data: CategoryItem[]; links: unknown[] };
}

function CategoryTile({ category }: { category: CategoryItem }) {
    const [imageFailed, setImageFailed] = useState(false);
    return (
        <Link
            href={route('market', { filter: { category: category.id } })}
            className="surface group hover:border-primary/40 overflow-hidden transition-colors"
        >
            <div className="bg-secondary/60 relative flex aspect-[4/3] items-center justify-center overflow-hidden border-b">
                {category.image_url && !imageFailed ? (
                    <img
                        src={category.image_url}
                        alt=""
                        className="size-full object-cover transition-transform duration-500 group-hover:scale-105"
                        loading="lazy"
                        onError={() => setImageFailed(true)}
                    />
                ) : (
                    <Boxes className="text-secondary-foreground/40 size-16 stroke-1" />
                )}
                <span className="bg-card group-hover:bg-primary group-hover:text-primary-foreground absolute right-4 bottom-4 flex size-9 items-center justify-center rounded-full border transition-colors">
                    <ArrowUpRight className="size-4" />
                </span>
            </div>
            <div className="p-5">
                <h2 className="text-base font-semibold tracking-tight">{category.name}</h2>
                <p className="text-muted-foreground mt-2 text-xs">
                    {category.products_count === undefined
                        ? 'Explore materials'
                        : `${category.products_count} ${category.products_count === 1 ? 'material' : 'materials'}`}
                </p>
            </div>
        </Link>
    );
}

export default function CategoryIndex({ categories }: CategoryProps) {
    return (
        <LandingLayout breadcrumbs={[{ title: 'Categories', href: '/categories' }]}>
            <Head title="Browse materials by category" />
            <div className="shop-container pt-8 sm:pt-10">
                <div className="mb-10 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
                    <div>
                        <p className="page-eyebrow mb-3">Find your foundation</p>
                        <h1 className="page-title">Every build starts somewhere.</h1>
                        <p className="text-muted-foreground mt-4 max-w-lg text-sm leading-6">
                            Find the right materials for each part of your project.
                        </p>
                    </div>
                    <Button asChild variant="outline" className="w-fit rounded-sm">
                        <Link href={route('market')}>
                            View all materials
                            <ArrowRight className="size-4" />
                        </Link>
                    </Button>
                </div>
                {categories.data.length > 0 ? (
                    <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        {categories.data.map((category) => (
                            <CategoryTile key={category.id} category={category} />
                        ))}
                    </div>
                ) : (
                    <div className="surface flex flex-col items-center px-6 py-20 text-center">
                        <span className="bg-secondary mb-5 rounded-sm p-4">
                            <Layers3 className="text-secondary-foreground size-8 stroke-1" />
                        </span>
                        <h2 className="text-xl font-semibold tracking-tight">The next layer is taking shape.</h2>
                        <p className="text-muted-foreground mt-3 text-sm">
                            There aren’t any categories to explore yet. You can still visit the market.
                        </p>
                        <Button asChild variant="outline" className="mt-6">
                            <Link href={route('market')}>
                                Explore the market
                                <ArrowRight />
                            </Link>
                        </Button>
                    </div>
                )}
                {categories.links?.length > 3 && (
                    <nav aria-label="Category pagination" className="mt-8 flex flex-wrap justify-center gap-2 border-t pt-6">
                        {categories.links.map((link, index) =>
                            link.url ? (
                                <Button asChild key={index} variant={link.active ? 'secondary' : 'outline'} size="sm">
                                    <Link href={link.url} aria-current={link.active ? 'page' : undefined}>
                                        {link.label.replace(/&laquo;|&raquo;/g, '').trim()}
                                    </Link>
                                </Button>
                            ) : (
                                <span key={index} className="text-muted-foreground flex h-8 items-center px-3 text-sm">
                                    {link.label.replace(/&laquo;|&raquo;/g, '').trim()}
                                </span>
                            ),
                        )}
                    </nav>
                )}
            </div>
        </LandingLayout>
    );
}
