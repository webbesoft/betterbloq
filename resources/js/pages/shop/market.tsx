import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Slider } from '@/components/ui/slider';
import LandingLayout from '@/layouts/landing-layout';
import { cn } from '@/lib/utils';
import { Category, PaginationBaseLinks, PaginationType, Product, Vendor } from '@/types/model-types';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, ArrowRight, Box, Loader2, RotateCcw, Search, SlidersHorizontal, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import ProductListItemListLayout from './components/product-list-item-list-layout';

interface MarketProps {
    products: { data: Product[]; meta: PaginationType; links: PaginationBaseLinks };
    filters: { filter?: { category?: string; vendor?: string; name?: string; price_between?: string }; category?: string; sort?: string };
    availableFilters: { categories: Category[]; vendors: Vendor[] };
}

export default function Market({ products, filters = {}, availableFilters }: MarketProps) {
    const initialFilters = Array.isArray(filters) ? {} : filters;
    const [category, setCategory] = useState(String(initialFilters.filter?.category ?? initialFilters.category ?? 'all'));
    const [vendor, setVendor] = useState(String(initialFilters.filter?.vendor ?? 'all'));
    const [priceRange, setPriceRange] = useState<number[]>(initialFilters.filter?.price_between?.split(':').map(Number) ?? [0, 1000]);
    const [searchQuery, setSearchQuery] = useState(initialFilters.filter?.name ?? '');
    const [sortBy, setSortBy] = useState(initialFilters.sort ?? '-created_at');
    const [filtersOpen, setFiltersOpen] = useState(false);
    const [loading, setLoading] = useState(false);
    const { data, meta, links } = products;
    const parameters = {
        filter: {
            category: category === 'all' ? undefined : category,
            vendor: vendor === 'all' ? undefined : vendor,
            name: searchQuery.trim() || undefined,
            price_between: priceRange[0] > 0 || priceRange[1] < 1000 ? `${priceRange[0]}:${priceRange[1]}` : undefined,
        },
        sort: sortBy,
    };
    const query = JSON.stringify(parameters);
    const lastQuery = useRef(query);
    const activeCount = [category !== 'all', vendor !== 'all', priceRange[0] > 0 || priceRange[1] < 1000].filter(Boolean).length;
    const clearFilters = () => {
        setCategory('all');
        setVendor('all');
        setPriceRange([0, 1000]);
        setSearchQuery('');
        setSortBy('-created_at');
    };

    useEffect(() => {
        if (query === lastQuery.current) return;
        const timeout = window.setTimeout(() => {
            lastQuery.current = query;
            router.get(route('market'), JSON.parse(query), {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                only: ['products', 'filters'],
                onStart: () => setLoading(true),
                onFinish: () => setLoading(false),
            });
        }, 300);
        return () => window.clearTimeout(timeout);
    }, [query]);

    return (
        <LandingLayout breadcrumbs={[{ title: 'Market', href: '/market' }]}>
            <Head title="The materials market" />
            <div className="shop-container pt-8 sm:pt-10">
                <div className="mb-9 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
                    <div>
                        <p className="page-eyebrow mb-3">The materials market</p>
                        <h1 className="page-title">Good things to build with.</h1>
                        <p className="text-muted-foreground mt-4 max-w-lg text-sm leading-6">
                            Find the right materials and put shared buying power behind your next project.
                        </p>
                    </div>
                    <div className="text-muted-foreground flex items-center gap-2 text-xs">
                        <Box className="size-4" />
                        <span>{meta?.total ?? 0} materials to explore</span>
                    </div>
                </div>
                <div className="grid items-start gap-7 lg:grid-cols-[240px_minmax(0,1fr)]">
                    <aside className="lg:sticky lg:top-26">
                        <Button
                            variant="outline"
                            className="mb-3 w-full justify-between lg:hidden"
                            onClick={() => setFiltersOpen(!filtersOpen)}
                            aria-expanded={filtersOpen}
                            aria-controls="market-filters"
                        >
                            <span className="flex items-center gap-2">
                                <SlidersHorizontal className="size-4" />
                                Filters{activeCount > 0 && ` (${activeCount})`}
                            </span>
                            <span>{filtersOpen ? 'Hide' : 'Show'}</span>
                        </Button>
                        <div id="market-filters" className={cn('surface p-5', filtersOpen ? 'block' : 'hidden lg:block')}>
                            <div className="mb-6 flex items-center justify-between border-b pb-4">
                                <h2 className="flex items-center gap-2 text-sm font-semibold">
                                    <SlidersHorizontal className="size-4" />
                                    Refine your search
                                </h2>
                                {activeCount > 0 && <span className="bg-secondary rounded-full px-2 py-0.5 text-xs">{activeCount}</span>}
                            </div>
                            <div className="space-y-6">
                                <div className="space-y-2.5">
                                    <Label htmlFor="market-category" className="text-xs">
                                        Category
                                    </Label>
                                    <Select value={category} onValueChange={setCategory}>
                                        <SelectTrigger id="market-category" className="bg-background/50 h-10 w-full">
                                            <SelectValue placeholder="All categories" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">All categories</SelectItem>
                                            {availableFilters.categories.map((item) => (
                                                <SelectItem key={item.id} value={String(item.id)}>
                                                    {item.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div className="space-y-2.5">
                                    <Label htmlFor="market-vendor" className="text-xs">
                                        Supplier
                                    </Label>
                                    <Select value={vendor} onValueChange={setVendor}>
                                        <SelectTrigger id="market-vendor" className="bg-background/50 h-10 w-full">
                                            <SelectValue placeholder="All suppliers" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">All suppliers</SelectItem>
                                            {availableFilters.vendors.map((item) => (
                                                <SelectItem key={item.id} value={String(item.id)}>
                                                    {item.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div>
                                    <Label id="market-price-label" className="text-xs">
                                        Unit price range
                                    </Label>
                                    <p className="mt-3 flex justify-between text-sm font-medium tabular-nums">
                                        <span>${priceRange[0]}</span>
                                        <span>
                                            ${priceRange[1]}
                                            {priceRange[1] === 1000 ? '+' : ''}
                                        </span>
                                    </p>
                                    <Slider
                                        aria-labelledby="market-price-label"
                                        min={0}
                                        max={1000}
                                        step={10}
                                        value={priceRange}
                                        onValueChange={setPriceRange}
                                        className="mt-5 mb-3"
                                    />
                                </div>
                            </div>
                            <Button
                                variant="ghost"
                                onClick={clearFilters}
                                disabled={activeCount === 0 && !searchQuery && sortBy === '-created_at'}
                                className="text-muted-foreground mt-6 w-full border-t pt-4 text-xs"
                            >
                                <RotateCcw className="size-3.5" />
                                Reset filters
                            </Button>
                        </div>
                        <div className="text-muted-foreground mt-5 hidden px-1 text-xs leading-6 lg:block">
                            <p className="text-foreground mb-1 font-medium">A little buying power goes a long way.</p>Pay upfront. Any unlocked volume
                            discount is refunded after your purchase cycle is finalized.
                        </div>
                    </aside>
                    <section aria-label="Materials" aria-busy={loading} className="min-w-0">
                        <div className="mb-6 flex flex-col gap-3 sm:flex-row">
                            <div className="relative flex-1">
                                <Search className="text-muted-foreground pointer-events-none absolute top-3.5 left-4 size-4" />
                                <Input
                                    type="search"
                                    value={searchQuery}
                                    onChange={(event) => setSearchQuery(event.target.value)}
                                    placeholder="Search materials…"
                                    aria-label="Search materials"
                                    className="bg-card h-11 rounded-sm pr-10 pl-11"
                                />
                                {searchQuery && (
                                    <button
                                        type="button"
                                        onClick={() => setSearchQuery('')}
                                        className="text-muted-foreground hover:text-foreground absolute top-0 right-0 flex h-11 w-10 items-center justify-center"
                                        aria-label="Clear search"
                                    >
                                        <X className="size-4" />
                                    </button>
                                )}
                            </div>
                            <Select value={sortBy} onValueChange={setSortBy}>
                                <SelectTrigger className="bg-card h-11 w-full rounded-sm sm:w-48" aria-label="Sort materials">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="-created_at">Recently added</SelectItem>
                                    <SelectItem value="created_at">Oldest first</SelectItem>
                                    <SelectItem value="name">Name: A to Z</SelectItem>
                                    <SelectItem value="-name">Name: Z to A</SelectItem>
                                    <SelectItem value="price">Price: low to high</SelectItem>
                                    <SelectItem value="-price">Price: high to low</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="text-muted-foreground mb-4 flex min-h-5 items-center justify-between text-xs" aria-live="polite">
                            <p>{searchQuery ? `Results for “${searchQuery}”` : 'Materials for your next milestone'}</p>
                            {loading && (
                                <span className="flex items-center gap-1.5">
                                    <Loader2 className="size-3 animate-spin" />
                                    Updating
                                </span>
                            )}
                        </div>
                        {data.length > 0 ? (
                            <div className={cn('grid gap-5 sm:grid-cols-2 xl:grid-cols-3', loading && 'opacity-60')}>
                                {data.map((product) => (
                                    <ProductListItemListLayout product={product} key={product.id} />
                                ))}
                            </div>
                        ) : (
                            <div className="surface flex flex-col items-center px-6 py-20 text-center">
                                <span className="bg-secondary mb-5 rounded-sm p-4">
                                    <Search className="text-secondary-foreground size-7" />
                                </span>
                                <h2 className="text-xl font-semibold tracking-tight">No materials found</h2>
                                <p className="text-muted-foreground mt-2 max-w-sm text-sm leading-6">
                                    Try a different search or clear your filters to explore the full market.
                                </p>
                                <Button variant="outline" onClick={clearFilters} className="mt-6">
                                    Clear filters
                                    <RotateCcw className="size-3.5" />
                                </Button>
                            </div>
                        )}
                        {meta?.last_page > 1 && (
                            <nav aria-label="Market pagination" className="mt-8 flex items-center justify-between border-t pt-5">
                                {links.prev ? (
                                    <Button asChild variant="outline" size="sm">
                                        <Link href={links.prev} preserveScroll>
                                            <ArrowLeft />
                                            Previous
                                        </Link>
                                    </Button>
                                ) : (
                                    <Button variant="outline" size="sm" disabled>
                                        <ArrowLeft />
                                        Previous
                                    </Button>
                                )}
                                <div className="hidden items-center gap-1 sm:flex">
                                    {meta.links.slice(1, -1).map((link, index) =>
                                        link.url ? (
                                            <Button key={index} asChild size="sm" variant={link.active ? 'secondary' : 'ghost'} className="size-9">
                                                <Link href={link.url} aria-current={link.active ? 'page' : undefined} preserveScroll>
                                                    {link.label}
                                                </Link>
                                            </Button>
                                        ) : (
                                            <span key={index} className="text-muted-foreground px-2 text-xs">
                                                …
                                            </span>
                                        ),
                                    )}
                                </div>
                                <span className="text-muted-foreground text-xs sm:hidden">
                                    {meta.current_page} / {meta.last_page}
                                </span>
                                {links.next ? (
                                    <Button asChild variant="outline" size="sm">
                                        <Link href={links.next} preserveScroll>
                                            Next
                                            <ArrowRight />
                                        </Link>
                                    </Button>
                                ) : (
                                    <Button variant="outline" size="sm" disabled>
                                        Next
                                        <ArrowRight />
                                    </Button>
                                )}
                            </nav>
                        )}
                    </section>
                </div>
            </div>
        </LandingLayout>
    );
}
