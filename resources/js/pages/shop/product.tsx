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
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Progress } from '@/components/ui/progress';
import { Separator } from '@/components/ui/separator';
import LandingLayout from '@/layouts/landing-layout';
import { formatDate } from '@/lib/helpers';
import { useCartStore } from '@/stores/use-cart-store';
import { Auth } from '@/types';
import { CartItem } from '@/types/cart';
import { Product, UserRating } from '@/types/model-types';
import { Head, Link, useForm } from '@inertiajs/react';
import { Calendar, CheckCircle, Info, ShoppingCart, Star, Tag, Users } from 'lucide-react';
import { FormEventHandler, useEffect, useMemo, useState } from 'react';
import { CountdownTimer } from './components/countdown-timer';
import { Carousel, CarouselContent, CarouselItem, CarouselNext, CarouselPrevious } from '@/components/ui/carousel';
import { Textarea } from '@headlessui/react';
import ReactMarkdown from 'react-markdown';
import remarkGfm from 'remark-gfm';
import { RatingStars } from './components/rating-stars';

const breadcrumbs = [
    { title: 'Market', href: route('market') },
    { title: 'Product Details', href: '#' },
];

interface ProductData {
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
    auth: Auth;
    hasOrder: boolean;
    canRate: boolean;
    userRating: UserRating;
}

interface PurchasePoolTierData {
    id: number;
    name: string;
    description?: string;
    discount_percentage: number;
    min_volume: number;
    max_volume?: number | null;
}

interface ActivePurchasePoolData {
    id: number;
    status: string;
    end_date?: string | null;
    target_delivery_date?: string | null;
    min_orders_for_discount: number;
    max_orders?: number | null;
    current_volume: number;
    target_volume: number;
    tiers: PurchasePoolTierData[];
    current_tier?: {
        id: number;
        name: string;
        discount_percentage: number;
        min_volume: number;
    } | null;
}

type OrderForm = {
    product_id: number;
    quantity: number;
    expected_delivery_date: string;
    purchase_pool_id?: number | null;
};

export default function ProductPage(props: ProductProps) {
    const { product, flash, activePurchasePool, hasOrder, canRate, userRating } = props;

    const { data: productData } = product;

    const addItemToCart = useCartStore((state) => state.addItem);
    const clearCart = useCartStore((state) => state.clearCart);
    const cartVendorId = useCartStore((state) => state.currentVendorId);
    const cartVendorName = useCartStore((state) => state.currentVendorName)

    const [showConfirmDialog, setShowConfirmDialog] = useState(false);
    const [productToAdd, setProductToAdd] = useState<Product | null>(null);

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
        purchase_pool_id: activePurchasePool?.id,
        quantity: 1,
        expected_delivery_date: '',
    });

    const [currentImage, setCurrentImage] = useState(productData.image || '');

    useEffect(() => {
        setCurrentImage(productData.image || '');
    }, [productData.image]);

    const allImages = [
        ...(productData.image ? [{ id: 'primary', url: productData.image }] : []),
        ...productData.additional_images,
    ];

    const getSubmitRoute = () => {
        return activePurchasePool ? route('orders.store') : route('purchase-pool-requests.store');
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        clearErrors();

        const targetRoute = getSubmitRoute();

        const dataToSend: Partial<OrderForm> = activePurchasePool
            ? {
                product_id: formdata.product_id,
                quantity: formdata.quantity,
                expected_delivery_date: formdata.expected_delivery_date,
                purchase_pool_id: activePurchasePool.id,
            }
            : {
                product_id: formdata.product_id,
            };

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

    const { data: ratingData, setData: setRatingData, post: postRating, processing: processingRating, errors: ratingErrors, reset: resetRating } = useForm({
        rating: userRating?.rating || 0,
        comment: userRating?.comment || '',
    });

    const [hoverRating, setHoverRating] = useState(0);

    const handleRatingSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        if (ratingData.rating > 0) {
            postRating(route('products.ratings.store', productData.id), {
                preserveScroll: true,
                onSuccess: () => {
                    resetRating('comment');
                },
                onError: (errs) => {
                    console.error("Rating submission error:", errs);
                }
            });
        } else {
            console.error("Please select a star rating.");
        }
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
            console.log(`${cartProduct.name} (x${quantityToAdd}) added to cart.`);
        }
    };

    const handleConfirmClearAndAdd = () => {
        if (!productToAdd) return;

        clearCart();
        const quantityToAdd = Math.max(1, Number(formdata.quantity));
        for (let i = 0; i < quantityToAdd; i++) {
            addItemToCart(productToAdd);
        }
        setShowConfirmDialog(false);
        setProductToAdd(null);
        console.log(`Cart cleared and ${productToAdd.name} (x${quantityToAdd}) added.`);
    };

    const getFormButton = () => {
        // 0. if no user
        if (!props.auth?.user) {
            return (
                <div className={'bg-secondary/50 flex flex-col items-center justify-start gap-2 rounded-md border p-4 text-center'}>
                    <p className={'text-foreground/80 text-sm font-medium'}>
                        <Info className="mr-1 inline h-4 w-4" />
                        You must be logged in to place an order.
                    </p>
                    <Link href={route('login')} className={'link text-sm'}>
                        Login
                    </Link>
                </div>
            );
        }

        if (activePurchasePool && hasOrder) {
            return (
                <div
                    className={
                        'flex flex-col items-center justify-start gap-2 rounded-md border bg-green-100 p-4 text-center text-green-700 dark:bg-green-900 dark:text-green-300'
                    }
                >
                    <CheckCircle className="mb-2 inline h-6 w-6" />
                    <p className={'text-sm font-medium'}>You have already placed an order for this product in the active purchase pool.</p>
                    {/* Optional: Link to view their order */}
                    {/* <Link href={route('orders.index')} className={'text-sm link mt-1'}>View My Orders</Link> */}
                </div>
            );
        }

        return (
            <div className={'flex flex-col items-center justify-start gap-2 rounded-md p-4 text-center'}>
                <Button type="submit" className="mt-auto w-full" disabled={processing || !activePurchasePool}>
                    <CheckCircle className="mr-2 h-4 w-4" />
                    {processing ? 'Placing Order...' : `Order Now & Join Pool (${currentDiscountPercent}% Off)`}
                </Button>
            </div>
        );
    };

    const currentDiscountPercent = activePurchasePool?.current_tier?.discount_percentage ?? 0;
    const pricePerUnit = productData.price;
    const discountedPricePerUnit = useMemo(() => {
        return pricePerUnit * (1 - currentDiscountPercent / 100);
    }, [pricePerUnit, currentDiscountPercent]);

    const total = useMemo(() => {
        return discountedPricePerUnit * formdata.quantity;
    }, [discountedPricePerUnit, formdata.quantity]);

    const renderPurchasePoolInfo = () => {
        if (!activePurchasePool) {
            return (
                <div className="border-border mt-4 rounded-md border border-dashed p-4 text-center">
                    <Info className="text-muted-foreground mx-auto mb-2 h-6 w-6" />
                    <p className="text-muted-foreground text-sm">No active purchase pool currently available for this product.</p>
                </div>
            );
        }

        const { current_volume, tiers, current_tier, end_date, max_orders, target_delivery_date, target_volume } = activePurchasePool;
        const nextTier = tiers.find((tier) => tier.min_volume > current_volume);
        const progressPercent = max_orders ? Math.min(100, (current_volume / max_orders) * 100) : 0;

        return (
            <div className="from-background to-secondary/30 mt-4 space-y-4 rounded-md border bg-gradient-to-br p-4">
                <div className="flex items-center justify-between">
                    <CardTitle className="flex items-center gap-2 text-lg">
                        <Users className="text-primary h-5 w-5" /> Active Purchase Pool
                    </CardTitle>
                    <Badge variant="default">Active</Badge>
                </div>

                {max_orders && max_orders > 0 && (
                    <div>
                        <div className="text-muted-foreground mb-1 flex justify-between text-xs font-medium">
                            <span>
                                Current Volume: {current_volume} / {target_volume}
                            </span>
                            {nextTier && <span>Next Tier at {nextTier.min_volume}</span>}
                        </div>
                        <Progress value={progressPercent} className="h-2 w-full" />
                        {nextTier && (
                            <p className="text-muted-foreground mt-1 text-right text-xs">
                                {nextTier.min_volume - current_volume} more needed for {nextTier.discount_percentage}% discount
                            </p>
                        )}
                    </div>
                )}
                {!max_orders && (
                    <p className="text-muted-foreground text-sm">
                        Current Volume: {current_volume} {productData.unit}
                    </p>
                )}

                {/* Display Tiers */}
                <div className="space-y-2">
                    <p className="text-foreground text-sm font-medium">Discount Tiers:</p>
                    <ul className="list-none space-y-1 pl-2">
                        {tiers.map((tier) => (
                            <li
                                key={tier.id}
                                className={`flex items-center gap-2 text-sm ${current_tier?.id === tier.id ? 'text-primary font-semibold' : 'text-muted-foreground'}`}
                            >
                                {current_tier?.id === tier.id ? <CheckCircle className="text-primary h-4 w-4" /> : <Tag className="h-4 w-4" />}
                                <span>
                                    {Number(tier.min_volume).toFixed(0)}
                                    {tier.max_volume ? ` - ${Number(tier.max_volume).toFixed(0)} units` : '+'}:{' '}
                                    <span className={current_tier?.id === tier.id ? 'text-foreground' : ''}>{tier.discount_percentage}% off</span>
                                </span>
                                {current_tier?.id === tier.id && (
                                    <Badge variant="outline" className="ml-auto">
                                        Current Tier
                                    </Badge>
                                )}
                            </li>
                        ))}
                    </ul>
                </div>

                {/* Dates */}
                <div className="grid grid-cols-2 gap-x-4 gap-y-1 text-sm">
                    {end_date && (
                        <div className="text-muted-foreground flex items-center gap-1">
                            <Calendar className="h-4 w-4" />
                            <span>Pool Ends:</span>
                            <span className="text-foreground font-medium">{formatDate(end_date)}</span>
                        </div>
                    )}
                    {target_delivery_date && (
                        <div className="text-muted-foreground flex items-center gap-1">
                            <Calendar className="h-4 w-4" />
                            <span>Target Delivery:</span>
                            <span className="text-foreground font-medium">{formatDate(target_delivery_date)}</span>
                        </div>
                    )}
                </div>
            </div>
        );
    };

    const isActionAreaDisabled = !props.auth?.user || (activePurchasePool && hasOrder);

    return (
        <LandingLayout breadcrumbs={breadcrumbs}>
            <Head title={productData.name} />
            <div className="container mx-auto h-full py-8">
                <div className="grid h-full grid-cols-1 gap-6 lg:grid-cols-2">
                    {/* Product Info Card (Left) */}
                    <Card className="flex h-full flex-col justify-between rounded-lg shadow-md md:order-1">
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <CardTitle className="text-xl font-semibold lg:text-2xl">{productData.name}</CardTitle>
                                {activePurchasePool && (
                                    <Badge variant="default" className="hidden md:inline-flex">
                                        Pool Active
                                    </Badge>
                                )}
                            </div>
                            <CardDescription className="text-muted-foreground">{productData.vendor.name}</CardDescription>
                            <div className="mt-1 flex items-center gap-2">
                                <RatingStars rating={productData.average_rating} />
                                {productData.ratings_count > 0 ? (
                                    <span className="text-sm text-muted-foreground">
                                        ({productData.ratings_count} {productData.ratings_count === 1 ? 'rating' : 'ratings'})
                                    </span>
                                ) : (
                                    <span className="text-sm text-muted-foreground">No ratings yet</span>
                                )}
                            </div>
                        </CardHeader>
                        <CardContent className="flex flex-grow flex-col items-start justify-start p-6">
                            <div className="bg-muted mb-4 aspect-video w-full overflow-hidden rounded-md border">
                                <img
                                    src={currentImage}
                                    alt={productData.name}
                                    className="h-full w-full object-contain"
                                />
                            </div>

                            {allImages.length > 1 && (
                                <div className="mb-4 w-full">
                                    <Carousel
                                        opts={{
                                            align: "start",
                                            loop: false,
                                        }}
                                        className="w-full max-w-full"
                                    >
                                        <CarouselContent className="-ml-2">
                                            {allImages.map((image, index) => (
                                                <CarouselItem key={image.id || index} className="basis-1/4 pl-2 md:basis-1/5 lg:basis-1/6">
                                                    <div className="aspect-square overflow-hidden rounded border">
                                                        <img
                                                            src={image.url}
                                                            alt={`Thumbnail ${index + 1}`}
                                                            className={`h-full w-full cursor-pointer object-cover transition-opacity hover:opacity-75 ${currentImage === image.url ? 'ring-2 ring-primary ring-offset-2' : ''}`}
                                                            onClick={() => setCurrentImage(image.url)}
                                                        // onError={(e) => { e.target.src = '/placeholder.png'; }}
                                                        />
                                                    </div>
                                                </CarouselItem>
                                            ))}
                                        </CarouselContent>
                                        <CarouselPrevious className="absolute left-2 top-1/2 -translate-y-1/2" />
                                        <CarouselNext className="absolute right-2 top-1/2 -translate-y-1/2" />
                                    </Carousel>
                                </div>
                            )}

                            <div className={'prose prose-sm sm:prose lg:prose-lg dark:prose-invert max-w-none text-foreground mb-4 text-base'}>
                                {/* <ReactMarkdown remarkPlugins={[remarkGfm]}>
                                    {productData.description}
                                </ReactMarkdown> */}
                                <p>
                                    {productData.description}
                                </p>
                            </div>

                            <div className="mt-auto w-full text-right">
                                {activePurchasePool && currentDiscountPercent > 0 ? (
                                    <>
                                        <p className="text-muted-foreground text-lg font-semibold line-through">
                                            ${pricePerUnit.toFixed(2)} / {productData.unit}
                                        </p>
                                        <p className="text-primary text-2xl font-bold">
                                            ${discountedPricePerUnit.toFixed(2)} / {productData.unit}
                                            <Badge variant="destructive" className="ml-2 align-middle">
                                                -{currentDiscountPercent}%
                                            </Badge>
                                        </p>
                                    </>
                                ) : (
                                    <p className="text-foreground text-xl font-semibold lg:text-2xl">
                                        ${pricePerUnit.toFixed(2)} / {productData.unit}
                                    </p>
                                )}
                            </div>

                            {canRate && (
                                <form onSubmit={handleRatingSubmit} className="mt-6 border-t pt-4 w-full">
                                    <h3 className="mb-2 text-lg font-medium">Rate this product</h3>
                                    {/* {props.flash?.success && (
                                        <div className="mb-4 rounded border border-green-300 bg-green-100 p-2 text-sm text-green-700">
                                            {props.flash.success}
                                        </div>
                                    )} */}
                                    <div className="mb-3">
                                        <Label htmlFor="rating" className="mb-1 block">Your Rating</Label>
                                        <div className="flex items-center">
                                            {[1, 2, 3, 4, 5].map((star) => (
                                                <Star
                                                    key={star}
                                                    className={`cursor-pointer ${(hoverRating || ratingData.rating) >= star ? 'text-yellow-400' : 'text-gray-300'}`}
                                                    fill={(hoverRating || ratingData.rating) >= star ? 'currentColor' : 'none'}
                                                    size={24}
                                                    onClick={() => setRatingData('rating', star)}
                                                    onMouseEnter={() => setHoverRating(star)}
                                                    onMouseLeave={() => setHoverRating(0)}
                                                />
                                            ))}
                                        </div>
                                        {ratingErrors.rating && <p className="mt-1 text-sm text-red-600">{ratingErrors.rating}</p>}
                                    </div>
                                    <div className="mb-3 flex flex-col">
                                        <Label htmlFor="comment">Your Comment (Optional)</Label>
                                        <Textarea
                                            id="comment"
                                            value={ratingData.comment}
                                            onChange={(e) => setRatingData('comment', e.target.value)}
                                            className="mt-1 border-grey-600 border rounded-md"
                                            rows={3}
                                        />
                                        {ratingErrors.comment && <p className="mt-1 text-sm text-red-600">{ratingErrors.comment}</p>}
                                    </div>
                                    <Button type="submit" disabled={processing || ratingData.rating === 0}>
                                        {processing ? 'Submitting...' : 'Submit Rating'}
                                    </Button>
                                </form>
                            )}

                            {!canRate && userRating && (
                                <div className="mt-6 border-t pt-4 w-full">
                                    <h3 className="mb-2 text-lg font-medium">Your Rating</h3>
                                    <RatingStars rating={userRating.rating} />
                                    {userRating.comment && (
                                        <p className="text-muted-foreground mt-2 text-sm italic">"{userRating.comment}"</p>
                                    )}
                                    {/* Optionally add an "Edit Rating" button here */}
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* Order/Pool Info Card (Right) */}
                    <Card className="flex h-full flex-col justify-between rounded-lg shadow-md md:order-2">
                        <CardHeader>
                            <CardTitle>Order {activePurchasePool && '& Purchase Pool'}</CardTitle>
                            <CardDescription>
                                {activePurchasePool ? 'Join the active purchase pool below to get a discount on your order of ' : 'Order '}
                                {productData.name}.
                            </CardDescription>
                            {activePurchasePool && (
                                <div className="bg-primary text-primary-foreground mt-4 rounded-md p-3 text-center font-bold">
                                    <p className="text-lg">Pool Closes In:</p>
                                    {/* <p className="text-2xl"> */}
                                    <CountdownTimer endDate={activePurchasePool.end_date!} />
                                    {/* </p> */}
                                </div>
                            )}
                        </CardHeader>
                        <CardContent className="flex-grow space-y-4">
                            {activePurchasePool ? renderPurchasePoolInfo() : renderPurchasePoolInfo()}{' '}
                            {activePurchasePool && <Separator className="my-6" />}
                            {activePurchasePool && hasOrder ? (
                                <div
                                    className={
                                        'flex flex-col items-center justify-start gap-2 rounded-md border bg-green-100 p-4 text-center text-green-700 dark:bg-green-900 dark:text-green-300'
                                    }
                                >
                                    <CheckCircle className="mb-2 inline h-6 w-6" />
                                    <p className={'text-sm font-medium'}>
                                        You have already placed an order for this product in the active purchase pool.
                                    </p>
                                    <Link href={route('orders.index')} className={'link mt-1 text-sm'}>
                                        View My Orders
                                    </Link>
                                </div>
                            ) : (
                                <form className="flex flex-col justify-start space-y-4" onSubmit={submit}>
                                    <div className="grid grid-cols-1 gap-2">
                                        <Label htmlFor="quantity">Quantity ({productData.unit})</Label>
                                        <Input
                                            type="number"
                                            id="quantity"
                                            name="quantity"
                                            className="col-span-3"
                                            onChange={(e) => setData('quantity', Math.max(1, Number(e.target.value)))}
                                            value={formdata.quantity}
                                            min="1"
                                            disabled={processing}
                                        />
                                        {errors.quantity && <p className="mt-1 text-sm text-red-500">{errors.quantity}</p>}
                                    </div>
                                    <div className="grid grid-cols-1 gap-2">
                                        <Label htmlFor="expected_delivery_date">Your Preferred Delivery Date (Optional)</Label>
                                        <Input
                                            type="date"
                                            id="expected_delivery_date"
                                            name="expected_delivery_date"
                                            className="text-foreground col-span-3"
                                            onChange={(e) => setData('expected_delivery_date', e.target.value)}
                                            value={formdata.expected_delivery_date}
                                            min={new Date().toISOString().split('T')[0]}
                                            disabled={processing}
                                        />
                                        {errors.expected_delivery_date && (
                                            <p className="mt-1 text-sm text-red-500">{errors.expected_delivery_date}</p>
                                        )}
                                    </div>
                                    <Separator />
                                    <div className="flex items-center justify-between">
                                        <p className="font-semibold">Order Total</p>
                                        <span className="text-foreground text-xl font-bold">
                                            ${total.toFixed(2)}
                                            {activePurchasePool && currentDiscountPercent > 0 && (
                                                <Badge variant="secondary" className="text-primary ml-2 align-middle">
                                                    {currentDiscountPercent}% off applied
                                                </Badge>
                                            )}
                                        </span>
                                    </div>
                                    <input type="hidden" name="product_id" value={productData.id} />
                                    {activePurchasePool && <input type="hidden" name="purchase_pool_pool_id" value={activePurchasePool.id} />}{' '}
                                    <div className="mt-auto pt-4">
                                        <Button
                                            type="button"
                                            variant="secondary"
                                            className="w-full space-y-2"
                                            onClick={handleAddToCartClick}
                                            disabled={processing || isActionAreaDisabled!}
                                        >
                                            <ShoppingCart className="mr-2 h-4 w-4" />
                                            Add to Cart
                                        </Button>
                                        {getFormButton()}
                                    </div>
                                </form>
                            )}
                        </CardContent>
                    </Card>
                </div>

                <AlertDialog open={showConfirmDialog} onOpenChange={setShowConfirmDialog}>
                    <AlertDialogContent className="bg-white">
                        <AlertDialogHeader>
                            <AlertDialogTitle>Different Vendor Alert</AlertDialogTitle>
                            <AlertDialogDescription>
                                Your cart contains items from a different vendor ({cartVendorName}). Adding this item will clear your current cart.
                                Do you want to proceed?
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
                            <AlertDialogAction onClick={handleConfirmClearAndAdd}>Clear Cart and Add</AlertDialogAction>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
            </div>
        </LandingLayout>
    );
}
