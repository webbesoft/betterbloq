import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Carousel, CarouselContent, CarouselItem, CarouselNext, CarouselPrevious } from '@/components/ui/carousel';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import LandingLayout from '@/layouts/landing-layout';
import { formatDate, parseAndCompareDates } from '@/lib/helpers';
import { useCartStore } from '@/stores/use-cart-store';
import { Auth } from '@/types';
import { CartItem } from '@/types/cart';
import { Product, UserRating } from '@/types/model-types';
import { Head, Link, useForm } from '@inertiajs/react';
import { CheckCircle, ExternalLink, Info, PackageSearch, ShoppingCart } from 'lucide-react';
import { FormEventHandler, useEffect, useMemo, useState } from 'react';
import ReactMarkdown from 'react-markdown';
import remarkGfm from 'remark-gfm';
import { toast } from 'sonner';
import { PurchasePoolInfo } from './components/product/purchase-pool-info';
import { ProductRating } from './components/product/ratings/product-rating';
import { RatingStars } from './components/rating-stars';

const breadcrumbs = [
    { title: 'Market', href: route('market') },
    { title: 'Product Details', href: '#' },
];

export interface ProductData {
    data: Product;
}

interface ProductProps {
    product: ProductData;
    flash: {
        message: {
            success?: string;
            error?: string;
            message?: string;
        };
    };
    hasPurchasePoolRequest: boolean;
    activePurchasePool: ActivePurchasePoolData | null;
    activePurchaseCycle: ActivePurchaseCycleData | null;
    auth: Auth;
    canRate: boolean;
    userRating: UserRating;
    defaultStorageRateMessage: string;
}

interface PurchasePoolTierData {
    id: number;
    name: string;
    description?: string;
    discount_percentage: number;
    min_volume: number;
    max_volume?: number | null;
}

export interface ActivePurchasePoolData {
    id: number;
    status: string;
    target_delivery_date?: string | null;
    min_orders_for_discount: number;
    max_orders?: number | null;
    current_volume: number;
    target_volume: number;
    tiers: PurchasePoolTierData[];
    end_date: string;
    start_date: string;
    current_tier?: {
        id: number;
        name: string;
        discount_percentage: number;
        min_volume: number;
    } | null;
}

export interface ActivePurchaseCycleData {
    id: number;
    start_date: string;
    end_date: string;
}

type OrderForm = {
    product_id: number;
    quantity: number;
    expected_delivery_date: string;
    purchase_cycle_id?: number;
    requires_storage_acknowledged?: boolean;
};

export default function ProductPage(props: ProductProps) {
    const { product, activePurchasePool, activePurchaseCycle, canRate, userRating, defaultStorageRateMessage } = props;

    const { data: productData } = product;

    const addItemToCart = useCartStore((state) => state.addItem);
    const clearCart = useCartStore((state) => state.clearCart);
    const cartVendorId = useCartStore((state) => state.currentVendorId);
    const cartVendorName = useCartStore((state) => state.currentVendorName);

    const [showConfirmDialog, setShowConfirmDialog] = useState(false);
    const [productToAdd, setProductToAdd] = useState<Product | null>(null);
    const [showStorageInfoMessage, setShowStorageInfoMessage] = useState(false);

    const {
        data: formdata,
        setData,
        post,
        processing,
        errors,
        reset,
        clearErrors,
    } = useForm<OrderForm>({
        product_id: productData.id,
        purchase_cycle_id: activePurchaseCycle?.id,
        quantity: 1,
        expected_delivery_date: '',
        requires_storage_acknowledged: false,
    });

    useEffect(() => {
        if (formdata.expected_delivery_date && activePurchasePool?.target_delivery_date) {
            const comparison = parseAndCompareDates(formdata.expected_delivery_date, activePurchasePool.target_delivery_date, 3);
            setShowStorageInfoMessage(comparison.needsStorage);
        } else {
            setShowStorageInfoMessage(false);
        }
    }, [formdata.expected_delivery_date, activePurchasePool?.target_delivery_date]);

    const [currentImage, setCurrentImage] = useState(productData.image || '');

    useEffect(() => {
        setCurrentImage(productData.image || '');
    }, [productData.image]);

    const allImages = [...(productData.image ? [{ id: 'primary', url: productData.image }] : []), ...productData.additional_images];

    const getSubmitRoute = () => {
        return activePurchasePool ? route('orders.store') : route('purchase-pool-requests.store');
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        clearErrors();

        const targetRoute = getSubmitRoute();

        post(targetRoute, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                if (activePurchasePool) {
                    reset('quantity', 'expected_delivery_date');
                } else {
                    // Handle success for pool request (e.g., navigate or show message)
                }
            },
            onError: (formErrors) => {
                console.error('Form submission error:', formErrors);
            },
        });
    };

    const handleAddToCartClick = () => {
        const cartProduct: CartItem = {
            ...productData,
            price: discountedPricePerUnit,
            vendor_id: productData.vendor.id,
            preparation_time: productData.preparation_time,
            image: productData.image,
            vendor_name: productData.vendor.name,
            quantity: formdata.quantity,
        };

        if (cartVendorId !== null && cartVendorId !== cartProduct.vendor.id) {
            setProductToAdd(cartProduct);
            setShowConfirmDialog(true);
        } else {
            const quantityToAdd = Math.max(1, Number(formdata.quantity));
            for (let i = 0; i < quantityToAdd; i++) {
                addItemToCart(cartProduct);
            }
            toast('Product added to cart.');
        }
    };

    const handleConfirmClearAndAdd = () => {
        if (!productToAdd) return;

        clearCart();
        const quantityToAdd = Math.max(1, Number(formdata.quantity));
        for (let i = 0; i < quantityToAdd; i++) {
            addItemToCart(productToAdd);
        }
        toast('Product added to cart.');
        setShowConfirmDialog(false);
        setProductToAdd(null);
    };

    const getFormButtonText = () => {
        if (processing) return 'Placing Order...';
        if (activePurchasePool) {
            return `Order Now & Join Pool (${currentDiscountPercent}% Off)`;
        }
        return 'Place Order';
    };

    const currentDiscountPercent = activePurchasePool?.current_tier?.discount_percentage ?? 0;
    const pricePerUnit = productData.price;
    const discountedPricePerUnit = useMemo(() => {
        return pricePerUnit * (1 - currentDiscountPercent / 100);
    }, [pricePerUnit, currentDiscountPercent]);

    const total = useMemo(() => {
        return discountedPricePerUnit * formdata.quantity;
    }, [discountedPricePerUnit, formdata.quantity]);

    const isActionAreaDisabled = !props.auth?.user || !activePurchasePool;

    return (
        <LandingLayout breadcrumbs={breadcrumbs}>
            {' '}
            {/* Pass auth to LandingLayout */}
            <Head title={productData.name} />
            <div className="container mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
                {/* Main Product Section */}
                <div className="space-y-8">
                    {/* Product Images & Key Info */}
                    <section aria-labelledby="product-information">
                        <div className="grid grid-cols-1 gap-6 md:grid-cols-2 md:gap-8">
                            {/* Image Gallery */}
                            <div className="space-y-4">
                                <div className="bg-muted/30 aspect-[4/3] w-full overflow-hidden rounded-lg border shadow-sm">
                                    <img
                                        src={currentImage}
                                        alt={productData.name}
                                        className="h-full w-full object-contain transition-opacity duration-300 hover:opacity-90"
                                        onError={(e) => {
                                            (e.target as HTMLImageElement).src =
                                                'https://placehold.co/600x400/e2e8f0/cbd5e0?text=Image+Not+Available';
                                        }}
                                    />
                                </div>
                                {allImages.length > 1 && (
                                    <Carousel opts={{ align: 'start', loop: allImages.length > 4 }} className="w-full">
                                        <CarouselContent className="-ml-2">
                                            {allImages.map((image, index) => (
                                                <CarouselItem key={image.id || index} className="basis-1/3 pl-2 sm:basis-1/4 md:basis-1/5">
                                                    <div
                                                        className={`aspect-square overflow-hidden rounded-md border transition-all hover:shadow-md ${currentImage === image.url ? 'ring-primary ring-2 ring-offset-2' : 'hover:ring-ring/50 hover:ring-1'}`}
                                                    >
                                                        <img
                                                            src={image.url}
                                                            alt={`Product thumbnail ${index + 1}`}
                                                            className="h-full w-full cursor-pointer object-cover"
                                                            onClick={() => setCurrentImage(image.url)}
                                                            onError={(e) => {
                                                                (e.target as HTMLImageElement).src =
                                                                    'https://placehold.co/100x100/e2e8f0/cbd5e0?text=Thumb';
                                                            }}
                                                        />
                                                    </div>
                                                </CarouselItem>
                                            ))}
                                        </CarouselContent>
                                        {allImages.length > 4 && (
                                            <>
                                                <CarouselPrevious className="absolute top-1/2 left-2 -translate-y-1/2 disabled:opacity-30" />
                                                <CarouselNext className="absolute top-1/2 right-2 -translate-y-1/2 disabled:opacity-30" />
                                            </>
                                        )}
                                    </Carousel>
                                )}
                            </div>

                            {/* Product Details & Price */}
                            <div className="flex flex-col space-y-4">
                                <h1
                                    id="product-information"
                                    className="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl dark:text-gray-50"
                                >
                                    {productData.name}
                                </h1>
                                <div className="flex hidden items-center space-x-2">
                                    <Link
                                        // href={route('vendors.show', productData.vendor.id)}
                                        href="#"
                                        className="text-muted-foreground hover:text-primary text-sm transition-colors"
                                    >
                                        {productData.vendor.name} <ExternalLink className="ml-1 inline-block h-3 w-3" />
                                    </Link>
                                </div>
                                <div className="flex items-center gap-2">
                                    <RatingStars rating={productData.average_rating} />
                                    {productData.ratings_count > 0 ? (
                                        <span className="text-muted-foreground text-sm">
                                            ({productData.ratings_count} {productData.ratings_count === 1 ? 'rating' : 'ratings'})
                                        </span>
                                    ) : (
                                        <span className="text-muted-foreground text-sm">No ratings yet</span>
                                    )}
                                </div>

                                <Separator />

                                {/* Price Section */}
                                <div className="mt-auto text-left">
                                    {activePurchasePool && currentDiscountPercent > 0 ? (
                                        <>
                                            <p className="text-muted-foreground text-xl font-medium line-through">
                                                ${pricePerUnit.toFixed(2)} / {productData.unit}
                                            </p>
                                            <p className="text-primary text-3xl font-bold sm:text-4xl">
                                                ${discountedPricePerUnit.toFixed(2)} / {productData.unit}
                                                <Badge variant="destructive" className="ml-2 align-middle text-sm">
                                                    -{currentDiscountPercent}%
                                                </Badge>
                                            </p>
                                        </>
                                    ) : (
                                        <p className="text-foreground text-3xl font-bold sm:text-4xl">
                                            ${pricePerUnit.toFixed(2)} / {productData.unit}
                                        </p>
                                    )}
                                </div>
                            </div>
                        </div>

                        {/* Purchase Pool Details (Full) */}
                        {activePurchasePool && (
                            <section aria-labelledby="purchase-pool-details-heading" className="pt-6">
                                <h2 id="purchase-pool-details-heading" className="sr-only">
                                    Purchase Pool Details
                                </h2>{' '}
                                <PurchasePoolInfo activePurchasePool={activePurchasePool} product={productData} />
                            </section>
                        )}
                    </section>

                    <Separator />

                    {/* Order Form & Actions Section */}
                    <section aria-labelledby="order-section">
                        {!props.auth?.user ? (
                            <div
                                className={
                                    'bg-secondary/30 flex flex-col items-center justify-center gap-3 rounded-lg border p-6 text-center shadow-sm'
                                }
                            >
                                <Info className="text-primary h-7 w-7" />
                                <p className={'text-foreground/90 text-md font-medium'}>
                                    You must be logged in to place an order or join a purchase pool.
                                </p>
                                <Button asChild className="mt-2">
                                    <Link href={route('login')}>Login or Create Account</Link>
                                </Button>
                            </div>
                        ) : (
                            activePurchasePool && (
                                <>
                                    <div
                                        className={
                                            'mb-6 flex flex-col items-center justify-center gap-3 rounded-lg border border-green-500 bg-green-50 p-6 text-center text-green-700 shadow-sm dark:border-green-600 dark:bg-green-900/30 dark:text-green-300'
                                        }
                                    >
                                        <CheckCircle className="mb-1 h-8 w-8" />
                                        <p className={'text-md font-semibold'}>You've already joined the pool for this product!</p>
                                        <p className="text-sm">You can view your order details in your account.</p>
                                        <Button
                                            variant="outline"
                                            asChild
                                            className="mt-2 border-green-500 text-green-700 hover:bg-green-100 dark:border-green-600 dark:text-green-300 dark:hover:bg-green-800/30"
                                        >
                                            <Link href={route('orders.index')}>View My Orders</Link>
                                        </Button>
                                    </div>

                                    <form onSubmit={submit} className="space-y-6 rounded-lg border bg-white p-6 shadow-lg dark:bg-gray-800">
                                        <h2 id="order-section" className="text-xl font-semibold text-gray-900 dark:text-gray-50">
                                            {activePurchasePool ? 'Join Purchase Pool & Order' : 'Place Your Order'}
                                        </h2>
                                        <div className="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                                            <div>
                                                <Label htmlFor="quantity" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Quantity ({productData.unit})
                                                </Label>
                                                <Input
                                                    type="number"
                                                    id="quantity"
                                                    name="quantity"
                                                    className="mt-1"
                                                    onChange={(e) => setData('quantity', Math.max(1, Number(e.target.value)))}
                                                    value={formdata.quantity}
                                                    min="1"
                                                    disabled={processing || isActionAreaDisabled!}
                                                />
                                                {errors.quantity && <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.quantity}</p>}
                                            </div>
                                            <div>
                                                <Label
                                                    htmlFor="expected_delivery_date"
                                                    className="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                                >
                                                    Preferred Delivery Date
                                                </Label>
                                                <Input
                                                    type="date"
                                                    id="expected_delivery_date"
                                                    name="expected_delivery_date"
                                                    className="text-foreground mt-1"
                                                    onChange={(e) => setData('expected_delivery_date', e.target.value)}
                                                    value={formdata.expected_delivery_date}
                                                    min={new Date(activePurchasePool.target_delivery_date ?? '').toISOString().split('T')[0]}
                                                    disabled={processing || isActionAreaDisabled!}
                                                    required
                                                />
                                                {errors.expected_delivery_date && (
                                                    <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.expected_delivery_date}</p>
                                                )}
                                            </div>
                                        </div>

                                        {showStorageInfoMessage && activePurchasePool && (
                                            <div className="border-primary/50 bg-primary/10 text-primary flex items-start gap-3 rounded-md border p-4 text-sm">
                                                <PackageSearch className="mt-0.5 h-5 w-5 flex-shrink-0" />
                                                <div>
                                                    <p className="font-semibold">Storage Information</p>
                                                    <p>
                                                        Your selected delivery date is significantly after the pool's target delivery date (
                                                        {formatDate(activePurchasePool.target_delivery_date!)}). This may require storage.
                                                    </p>
                                                    <p className="mt-1">
                                                        {defaultStorageRateMessage
                                                            ? `Storage is typically charged at ${defaultStorageRateMessage}. `
                                                            : 'Storage fees may apply. '}
                                                        Final costs will be confirmed after your order.
                                                    </p>
                                                </div>
                                            </div>
                                        )}

                                        <Separator />

                                        <div className="flex flex-col items-stretch gap-4 sm:flex-row sm:items-center sm:justify-between">
                                            <div className="text-left">
                                                <p className="text-sm font-medium text-gray-700 dark:text-gray-300">Order Total</p>
                                                <span className="text-foreground text-2xl font-bold">
                                                    ${total.toFixed(2)}
                                                    {activePurchasePool && currentDiscountPercent > 0 && (
                                                        <Badge variant="secondary" className="text-primary ml-2 align-middle text-xs">
                                                            {currentDiscountPercent}% off applied
                                                        </Badge>
                                                    )}
                                                </span>
                                            </div>
                                            <div className="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    className="w-full sm:w-auto"
                                                    onClick={handleAddToCartClick}
                                                    disabled={processing || isActionAreaDisabled || !formdata.quantity || formdata.quantity < 1}
                                                >
                                                    <ShoppingCart className="mr-2 h-4 w-4" />
                                                    Add to Cart
                                                </Button>
                                                <Button
                                                    type="submit"
                                                    className="w-full sm:w-auto"
                                                    disabled={
                                                        processing ||
                                                        isActionAreaDisabled ||
                                                        !formdata.expected_delivery_date ||
                                                        !formdata.quantity ||
                                                        formdata.quantity < 1
                                                    }
                                                >
                                                    <CheckCircle className="mr-2 h-4 w-4" />
                                                    {getFormButtonText()}
                                                </Button>
                                            </div>
                                        </div>
                                        {!formdata.expected_delivery_date && activePurchasePool && !isActionAreaDisabled && (
                                            <p className="text-destructive mt-2 text-center text-xs sm:text-left">
                                                Please select your preferred delivery date to order.
                                            </p>
                                        )}
                                    </form>
                                </>
                            )
                        )}
                    </section>

                    {/* Product Description */}
                    <section aria-labelledby="product-description-heading" className="pt-6">
                        <Card className="shadow-md">
                            <CardHeader>
                                <CardTitle id="product-description-heading" className="text-xl font-semibold lg:text-2xl">
                                    Product Description
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className={'prose prose-sm sm:prose-base lg:prose-lg dark:prose-invert text-foreground max-w-none'}>
                                    <ReactMarkdown remarkPlugins={[remarkGfm]}>
                                        {productData.description || 'No description available.'}
                                    </ReactMarkdown>
                                </div>
                            </CardContent>
                        </Card>
                    </section>

                    {!activePurchasePool &&
                        props.auth?.user && ( // Show if no active pool and user is logged in
                            <section aria-labelledby="request-pool-heading" className="pt-6">
                                <div className="border-border mt-6 rounded-md border border-dashed p-6 text-center">
                                    <Info className="text-muted-foreground mx-auto mb-3 h-8 w-8" />
                                    <p className="text-muted-foreground text-base">No active purchase pool currently available for this product.</p>
                                </div>
                            </section>
                        )}

                    {/* User Ratings & Reviews Section */}
                    {(canRate || userRating || productData.ratings_count > 0) && <Separator className="my-8" />}

                    <section aria-labelledby="ratings-reviews-heading" className="space-y-6">
                        <h2 id="ratings-reviews-heading" className="text-xl font-semibold text-gray-900 lg:text-2xl dark:text-gray-50">
                            Ratings & Reviews
                        </h2>
                        {canRate && <ProductRating product={productData} userRating={userRating} />}

                        {!canRate && userRating && (
                            <Card className="shadow-md">
                                <CardHeader>
                                    <CardTitle className="text-lg">Your Rating</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <RatingStars rating={userRating.rating} size={24} />
                                    {userRating.comment && (
                                        <p className="text-muted-foreground border-border mt-3 border-l-4 pl-3 text-sm italic">
                                            "{userRating.comment}"
                                        </p>
                                    )}
                                </CardContent>
                            </Card>
                        )}

                        {productData.ratings_count > 0 && !userRating && !canRate && (
                            <p className="text-muted-foreground">
                                Product has {productData.ratings_count} ratings. Login to see if you can rate it or view your past rating.
                            </p>
                        )}
                        {productData.ratings_count === 0 && !canRate && <p className="text-muted-foreground">Be the first to rate this product!</p>}
                    </section>
                </div>

                {/* Alert Dialog for Cart */}
                <AlertDialog open={showConfirmDialog} onOpenChange={setShowConfirmDialog}>
                    <AlertDialogContent className="bg-background rounded-lg shadow-xl">
                        <AlertDialogHeader>
                            <AlertDialogTitle>Clear Cart?</AlertDialogTitle>
                            <AlertDialogDescription>
                                Your cart contains items from a different vendor ({cartVendorName}). Adding this item will clear your current cart. Do
                                you want to proceed?
                            </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                            <AlertDialogCancel
                                onClick={() => {
                                    setShowConfirmDialog(false);
                                    setProductToAdd(null);
                                }}
                            >
                                Cancel
                            </AlertDialogCancel>
                            <AlertDialogAction
                                onClick={handleConfirmClearAndAdd}
                                className="bg-destructive hover:bg-destructive/90 text-destructive-foreground"
                            >
                                Clear Cart and Add
                            </AlertDialogAction>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
            </div>
        </LandingLayout>
    );
}
