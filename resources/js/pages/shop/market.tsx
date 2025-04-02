import EmptyResource from '@/components/empty-resource';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Pagination, PaginationContent, PaginationItem, PaginationLink, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import { PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Slider } from '@/components/ui/slider';
import AppLayout from '@/layouts/app-layout';
import { debounce } from '@/lib/helpers';
import { type BreadcrumbItem } from '@/types';
import { PaginationBaseLinks, PaginationType, Product } from '@/types/model-types';
import { Head, router } from '@inertiajs/react';
import { Popover } from '@radix-ui/react-popover';
import { SetStateAction, useCallback, useEffect, useState } from 'react';
import ProductListItem from './components/product-list-item';

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
    filters: any;
    availableFilters: any;
}

export default function Market(props: MarketProps) {
    const { products, filters: initialFilters, availableFilters } = props;

    const [category, setCategory] = useState(initialFilters.category || 'all');
    const [vendor, setVendor] = useState(initialFilters.vendor || 'all');
    const [priceRange, setPriceRange] = useState([initialFilters.price_from || 0, initialFilters.price_to || 1000]);
    const [searchQuery, setSearchQuery] = useState<string>(initialFilters['searchQuery'] || '');
    const [sortBy, setSortBy] = useState(initialFilters.sort ? initialFilters.sort[0] : '-created_at');

    const { data, meta, links } = products;

    const paginationLinks = () => {
        if (!meta || !meta.links) {
            return null;
        }

        return meta.links
            .filter((link) => link.active)
            .map((link, index) => (
                <PaginationLink key={index} href={link.url} size={1} className="rounded-sm" dangerouslySetInnerHTML={{ __html: link.label }} />
            ));
    };

    const MAX_PRICE = 1000;

    const reload = useCallback(
        debounce((newFilters) => {
            console.log('Requesting with filters:', newFilters);
            router.get(route('market'), newFilters, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                only: ['products'],
            });
        }, 300),
        [],
    );

    useEffect(() => {
        if (
            category !== (initialFilters.category || 'all') ||
            vendor !== (initialFilters.vendor || 'all') ||
            sortBy !== (initialFilters.sort || '-created_at') ||
            priceRange[0] !== (initialFilters.price_from || 0) ||
            priceRange[1] !== (initialFilters.price_to || 1000)
        ) {
            const currentFilters = {
                filter: {
                    category: category === 'all' ? undefined : category,
                    vendor: vendor === 'all' ? undefined : vendor,

                    price_between: priceRange[0] > 0 || priceRange[1] < 1000 ? `${priceRange[0]}:${priceRange[1]}` : undefined,

                    name: searchQuery || undefined,
                },
                sort: {
                    sort: sortBy === '-created_at' ? undefined : sortBy,
                },
            };
            Object.keys(currentFilters).forEach((key) => currentFilters[key] === undefined && delete currentFilters[key]);
            reload(currentFilters);
        }
    }, [category, vendor, sortBy, searchQuery, priceRange]);

    const handleCategoryChange = (value: string) => setCategory(value);
    const handleVendorChange = (value: string) => setVendor(value);
    const handleSearchQueryChange = (value: string) => setSearchQuery(value);
    const handleSortChange = (value) => setSortBy(value);

    const handlePriceChange = (newRange: SetStateAction<unknown[]>) => {
        setPriceRange(newRange);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Market" />
            <div className="h-full gap-4 rounded-xl p-4">
                <div className="flex w-full flex-shrink-0 items-center justify-start">
                    {' '}
                    <div className="flex w-full flex-col items-start justify-start gap-4">
                        <Input placeholder="Search" value={searchQuery} onChange={(e) => handleSearchQueryChange(e.target.value)} />
                        <div className="flex w-full items-center justify-start gap-4">
                            <div className="flex w-full items-center justify-between gap-2">
                                <div className="flex w-3/4 items-center justify-start gap-2">
                                    <Select value={category} onValueChange={handleCategoryChange}>
                                        <SelectTrigger className="rounded-md">
                                            {' '}
                                            <span className="block text-xs text-gray-500">Category</span>
                                            <SelectValue placeholder="Select Category" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">All Categories</SelectItem>
                                            {availableFilters.categories.map((cat) => (
                                                <SelectItem key={cat.id} value={String(cat.id)}>
                                                    {' '}
                                                    {/* Ensure value is string if needed by Select */}
                                                    {cat.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <Select value={vendor} onValueChange={handleVendorChange}>
                                        <SelectTrigger className="rounded-md">
                                            {' '}
                                            <span className="block text-xs text-gray-500">Vendor</span>
                                            <SelectValue placeholder="Select Vendor" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">All Vendors</SelectItem>
                                            {availableFilters.vendors.map((ven) => (
                                                <SelectItem key={ven.id} value={String(ven.id)}>
                                                    {' '}
                                                    {ven.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>

                                    <Popover>
                                        <PopoverTrigger asChild>
                                            {/* This button opens the popover */}
                                            <Button
                                                variant="outline"
                                                className="h-10 w-full justify-start rounded-sm px-4 py-2 text-left font-normal"
                                            >
                                                {/* Display current range */}
                                                <div>
                                                    <span className="text-xxs block text-gray-500">Price</span>
                                                    <span className="text-sm">
                                                        From ${priceRange[0]} - ${priceRange[1]}
                                                        {priceRange[1] === MAX_PRICE ? '+' : ''}
                                                    </span>{' '}
                                                    {/* Optional: Add '+' if max is selected */}
                                                </div>
                                            </Button>
                                        </PopoverTrigger>
                                        <PopoverContent className="w-60 p-4" align="start">
                                            {' '}
                                            {/* Adjust width/padding */}
                                            <div className="space-y-4">
                                                <Label htmlFor="price-range-slider" className="block text-center">
                                                    Price Range (${priceRange[0]} - ${priceRange[1]})
                                                </Label>
                                                <Slider
                                                    id="price-range-slider"
                                                    min={0}
                                                    max={MAX_PRICE}
                                                    step={10} // Adjust step value as needed (e.g., 1, 5, 10, 50)
                                                    value={priceRange} // Bind to state
                                                    onValueChange={handlePriceChange} // Update state on change
                                                    className="my-4" // Add some margin
                                                />
                                                {/* Optional: Display min/max values clearly */}
                                                <div className="text-muted-foreground flex justify-between text-sm">
                                                    <span>${priceRange[0]}</span>
                                                    <span>${priceRange[1]}</span>
                                                </div>
                                            </div>
                                        </PopoverContent>
                                    </Popover>
                                </div>
                                <div className="w-48 flex-shrink-0">
                                    <Select value={sortBy} onValueChange={handleSortChange}>
                                        <SelectTrigger className="rounded-full">
                                            <span className="block text-xs text-gray-500">Sort</span>
                                            <SelectValue placeholder="Sort By" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="-created_at">Newest First</SelectItem>
                                            <SelectItem value="created_at">Oldest First</SelectItem>
                                            <SelectItem value="name">Name (A-Z)</SelectItem>
                                            <SelectItem value="-name">Name (Z-A)</SelectItem>
                                            <SelectItem value="price">Price (Low to High)</SelectItem>
                                            <SelectItem value="-price">Price (High to Low)</SelectItem>
                                            {/* Add other sorting options as needed */}
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div className="py-4">
                    {data.length === 0 && <EmptyResource type="products" />}
                    <div className="grid grid-cols-4 gap-4">
                        {data.map((product: Product) => {
                            return (
                                <div>
                                    <ProductListItem product={product} key={product.id} />
                                </div>
                            );
                        })}
                    </div>
                </div>
                <div className="mb-0">
                    {meta && meta.total > 1 && (
                        <Pagination>
                            <PaginationContent className="justify-center gap-2">
                                <PaginationItem>
                                    <PaginationPrevious hidden={!links.prev} href={links.prev} size={1} />
                                </PaginationItem>
                                <PaginationItem className="p-2 shadow-sm">{paginationLinks()}</PaginationItem>
                                {/* <PaginationItem>
                                        <PaginationEllipsis />
                                    </PaginationItem> */}
                                <PaginationItem>
                                    <PaginationNext hidden={!links.next} href={links.next} size={1} />
                                </PaginationItem>
                            </PaginationContent>
                        </Pagination>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
