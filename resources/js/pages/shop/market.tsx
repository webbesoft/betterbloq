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
import LandingLayout from '@/layouts/landing-layout';
import ProductListItemListLayout from './components/product-list-item-list-layout';
import { ChevronDown } from 'lucide-react';

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
                <PaginationLink key={index} href={link.url} size={1}  className="rounded-sm" dangerouslySetInnerHTML={{ __html: link.label }} />
            ));
    };

    const MAX_PRICE = 1000;

    const reload = useCallback(
        debounce((newFilters: any) => {
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
        <LandingLayout breadcrumbs={breadcrumbs}>
            <Head title="Market" />
            <div className="container mx-auto grid grid-cols-1 gap-6 p-4 lg:grid-cols-4">
    
            <aside className="lg:col-span-1">
        
                        <div className="space-y-6 rounded-lg border bg-card p-4 shadow-sm">
        
                            <div>
                                <h3 className="mb-2 text-sm font-semibold uppercase text-muted-foreground">Category</h3>
                                <Select value={category} onValueChange={handleCategoryChange}>
                                    <SelectTrigger className="w-full rounded-md"> 
        
                                        <SelectValue placeholder="All Categories" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Categories</SelectItem>
                                        {availableFilters?.categories?.map((cat) => (
                                            <SelectItem key={cat.id} value={String(cat.id)}>
                                                {cat.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            
                            <div>
                                <h3 className="mb-2 text-sm font-semibold uppercase text-muted-foreground">Vendor</h3>
                                <Select value={vendor} onValueChange={handleVendorChange}>
                                    <SelectTrigger className="w-full rounded-md">
                                        <SelectValue placeholder="All Vendors" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Vendors</SelectItem>
                                        {availableFilters?.vendors?.map((ven) => (
                                            <SelectItem key={ven.id} value={String(ven.id)}>
                                                {ven.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                        
                            <div>
                                <h3 className="mb-2 text-sm font-semibold uppercase text-muted-foreground">Price Range</h3>
                                <Popover>
                                    <PopoverTrigger asChild>
                                        <Button
                                            variant="outline"
                                            className="h-10 w-full justify-between rounded-md px-3 py-2 text-left font-normal" // Full width, adjusted padding
                                        >
                                            
                                            <span className="text-sm">
                                                ${priceRange[0]} - ${priceRange[1]}
                                                {priceRange[1] === MAX_PRICE ? '+' : ''}
                                            </span>
                                            
                                             <ChevronDown className="h-4 w-4 opacity-50" />
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent className="w-60 p-4" align="start">
                                        <div className="space-y-4">
                                            <Label htmlFor="price-range-slider-side" className="block text-center">
                                                Price Range (${priceRange[0]} - ${priceRange[1]})
                                            </Label>
                                            <Slider
                                                id="price-range-slider-side"
                                                min={0}
                                                max={MAX_PRICE}
                                                step={10}
                                                value={priceRange}
                                                onValueChange={handlePriceChange}
                                                className="my-4"
                                            />
                                            <div className="text-muted-foreground flex justify-between text-sm">
                                                <span>${0}</span>
                                                <span>${MAX_PRICE}{MAX_PRICE === priceRange[1] ? '+' : ''}</span>
                                            </div>
                                        </div>
                                    </PopoverContent>
                                </Popover>
                            </div>

                            {/* Optional: Add more filters here (e.g., Rating) */}
                            {/* <Separator className="my-4" /> */}
                            {/* <div> */}
                            {/* <h3 className="mb-2 text-sm font-semibold uppercase text-muted-foreground">Rating</h3> */}
                                {/* Add Rating filter component */}
                            {/* </div> */}

                            <Button variant="ghost" className="w-full justify-center">Clear All Filters</Button>
                        </div>
                    </aside>
    
            
                <main className="lg:col-span-3">
                    {/* Top Bar: Search and Sort */}
                    <div className="mb-4 flex flex-col gap-4 rounded-lg border bg-card p-4 shadow-sm md:flex-row md:items-center md:justify-between">
                       <div className="flex-grow">
                           <Input placeholder="Search Market..." value={searchQuery} onChange={(e) => handleSearchQueryChange(e.target.value)} />
                       </div>
                       <div className="w-full flex-shrink-0 md:w-48">
                           {/* Keep Sort Dropdown */}
                           <Select value={sortBy} onValueChange={handleSortChange}>
                                    <SelectTrigger className="w-full rounded-md">
                                    
                                        <SelectValue placeholder="Sort By" />
                                    </SelectTrigger>
                                    <SelectContent>
                                    
                                        <SelectItem value="-created_at">Newest First</SelectItem>
                                        <SelectItem value="created_at">Oldest First</SelectItem>
                                        <SelectItem value="name">Name (A-Z)</SelectItem>
                                        <SelectItem value="-name">Name (Z-A)</SelectItem>
                                        <SelectItem value="price">Price (Low to High)</SelectItem>
                                        <SelectItem value="-price">Price (High to Low)</SelectItem>
                                        {/* <SelectItem value="-rating">Rating (High to Low)</SelectItem> */}
                                    </SelectContent>
                                </Select>
                       </div>
                    </div>
    
                    
                     <div className="mb-4 flex items-center justify-between text-sm text-muted-foreground">
                        <span>Showing {meta?.total || 0} results</span>
                        {/* Add logic to display active filters here */}
            
                     </div>
    
    
                    {/* Product Grid */}
                    {/* {data.length === 0 && !isLoading && <EmptyResource type="products" />} {/* Add loading state check */}
                    {/* {isLoading && <div>Loading products...</div>} Add Loading state */}
    
                    <div className="space-y-4">
                        {data.map((product: Product) => (
                           
                           <ProductListItemListLayout product={product} key={product.id} />
                        ))}
                    </div>
    
                    {/* Pagination */}
                    <div className="mt-6">
                    {meta && meta.last_page > 1 && (
                        <Pagination>
                            <PaginationContent className="justify-center gap-1 sm:gap-2"> {/* Adjust gap */}
                                <PaginationItem>
                                    <PaginationPrevious href={links?.prev || '#'} disabled={!!links?.prev} size={1} />
                                </PaginationItem>
                                <PaginationItem className="hidden items-center gap-1 rounded-md border p-1 px-2 shadow-sm sm:flex">
                                     {paginationLinks()}
                                </PaginationItem>
                                 <PaginationItem className="p-2 text-sm sm:hidden">
                                      Page {meta.current_page} of {meta.last_page}
                                 </PaginationItem>

                                <PaginationItem>
                                      
                                    <PaginationNext href={links?.next || '#'} disabled={!!links?.next} size={1} />
                                </PaginationItem>
                            </PaginationContent>
                        </Pagination>
                    )}
                </div>
                </main>
            </div>
        </LandingLayout>
    );
}
