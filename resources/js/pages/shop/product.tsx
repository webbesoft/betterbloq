import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { Auth, type BreadcrumbItem } from '@/types';
import { Product } from '@/types/model-types';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler, useEffect, useMemo, useState } from 'react';
import { Calendar, CheckCircle, Clock, Info, Tag, Users } from 'lucide-react';
import LandingLayout from '@/layouts/landing-layout';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { formatDate } from '@/lib/helpers';
import { CountdownTimer } from './components/countdown-timer';

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
        }
    },
    hasPurchasePoolRequest: boolean;
    activePurchasePool: ActivePurchasePoolData | null;
    auth: Auth;
    hasOrder: boolean;
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

export default function Market(props: ProductProps) {
    const { product, flash, activePurchasePool, hasOrder } = props;

    const { data: productData } = product;

    const {
        data: formdata,
        setData,
        post,
        processing,
        errors,
        reset,
        clearErrors
    } = useForm<OrderForm>({
        product_id: productData.id,
        purchase_pool_id: activePurchasePool?.id,
        quantity: 1,
        expected_delivery_date: '',
    });

    const getSubmitRoute = () => {
        return activePurchasePool
            ? route('orders.store')
            : route('purchase-pool-requests.store');
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
                purchase_pool_id: activePurchasePool.id
            }
            : { // Sending pool request data
                product_id: formdata.product_id
                // Add quantity/other fields if needed for pool requests
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
                console.error("Form submission error:", formErrors);
            },
        });
    };

    const getFormButton = () => {
        // 0. if no user
        if (!props.auth?.user) {
            return (
                <div className={'flex flex-col items-center justify-start gap-2 text-center p-4 border rounded-md bg-secondary/50'}>
                    <p className={'text-sm font-medium text-foreground/80'}>
                        <Info className="inline h-4 w-4 mr-1" />
                        You must be logged in to place an order.
                    </p>
                    <Link href={route('login')} className={'text-sm link'}>Login</Link>
                </div>
            )
        }

        if (activePurchasePool && hasOrder) {
            return (
                <div className={'flex flex-col items-center justify-start gap-2 text-center p-4 border rounded-md bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'}>
                    <CheckCircle className="inline h-6 w-6 mb-2" />
                    <p className={'text-sm font-medium'}>
                        You have already placed an order for this product in the active purchase pool.
                    </p>
                    {/* Optional: Link to view their order */}
                    {/* <Link href={route('orders.index')} className={'text-sm link mt-1'}>View My Orders</Link> */}
                </div>
            );
        }

        // 2. There's an active pool to join
        return (
            <div className={'flex flex-col items-center justify-start gap-2 text-center p-4 rounded-md'}>
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
                <div className="mt-4 rounded-md border border-dashed border-border p-4 text-center">
                    <Info className="mx-auto h-6 w-6 text-muted-foreground mb-2" />
                    <p className="text-sm text-muted-foreground">
                        No active purchase pool currently available for this product.
                    </p>
                </div>
            );
        }

        const { current_volume, tiers, current_tier, end_date, max_orders, target_delivery_date, target_volume } = activePurchasePool;
        const nextTier = tiers.find(tier => tier.min_volume > current_volume);
        const progressPercent = max_orders ? Math.min(100, (current_volume / max_orders) * 100) : 0;

        return (
            <div className="space-y-4 mt-4 rounded-md border p-4 bg-gradient-to-br from-background to-secondary/30">
                <div className='flex justify-between items-center'>
                    <CardTitle className="text-lg flex items-center gap-2">
                        <Users className="h-5 w-5 text-primary" /> Active Purchase Pool
                    </CardTitle>
                    <Badge variant="default">Active</Badge>
                </div>

                {max_orders && max_orders > 0 && (
                    <div>
                        <div className="mb-1 flex justify-between text-xs font-medium text-muted-foreground">
                            <span>Current Volume: {current_volume} / {target_volume}</span>
                            {nextTier && <span>Next Tier at {nextTier.min_volume}</span>}
                        </div>
                        <Progress value={progressPercent} className="w-full h-2" />
                        {nextTier && (
                            <p className="text-xs text-muted-foreground mt-1 text-right">
                                {nextTier.min_volume - current_volume} more needed for {nextTier.discount_percentage}% discount
                            </p>
                        )}
                    </div>
                )}
                {!max_orders && (
                    <p className="text-sm text-muted-foreground">Current Volume: {current_volume} {productData.unit}</p>
                )}


                {/* Display Tiers */}
                <div className="space-y-2">
                    <p className="text-sm font-medium text-foreground">Discount Tiers:</p>
                    <ul className="list-none space-y-1 pl-2">
                        {tiers.map((tier) => (
                            <li key={tier.id} className={`text-sm flex items-center gap-2 ${current_tier?.id === tier.id ? 'font-semibold text-primary' : 'text-muted-foreground'}`}>
                                {current_tier?.id === tier.id ? <CheckCircle className="h-4 w-4 text-primary" /> : <Tag className="h-4 w-4" />}
                                <span>{Number(tier.min_volume).toFixed(0)}{tier.max_volume ? ` - ${Number(tier.max_volume).toFixed(0)} units` : '+'}: <span className={current_tier?.id === tier.id ? "text-foreground" : ""}>{tier.discount_percentage}% off</span></span>
                                {current_tier?.id === tier.id && <Badge variant="outline" className='ml-auto'>Current Tier</Badge>}
                            </li>
                        ))}
                    </ul>
                </div>

                {/* Dates */}
                <div className="grid grid-cols-2 gap-x-4 gap-y-1 text-sm">
                    {end_date && (
                        <div className="flex items-center gap-1 text-muted-foreground">
                            <Calendar className="h-4 w-4" />
                            <span>Pool Ends:</span>
                            <span className='font-medium text-foreground'>{formatDate(end_date)}</span>
                        </div>
                    )}
                    {target_delivery_date && (
                        <div className="flex items-center gap-1 text-muted-foreground">
                            <Calendar className="h-4 w-4" />
                            <span>Target Delivery:</span>
                            <span className='font-medium text-foreground'>{formatDate(target_delivery_date)}</span>
                        </div>
                    )}
                </div>
            </div>
        );
    };

    return (
        <LandingLayout breadcrumbs={breadcrumbs}>
            <Head title={productData.name} />
            <div className="container mx-auto h-full py-8">
                <div className="grid h-full grid-cols-1 gap-6 lg:grid-cols-2">

                    {/* Product Info Card (Left) */}
                    <Card className="flex h-full flex-col justify-between rounded-lg shadow-md md:order-1">
                        <CardHeader>
                            <div className="flex justify-between items-center">
                                <CardTitle className="text-xl lg:text-2xl font-semibold">{productData.name}</CardTitle>
                                {activePurchasePool && <Badge variant="default" className='hidden md:inline-flex'>Pool Active</Badge>}
                            </div>
                            <CardDescription className="text-muted-foreground">{productData.vendor.name}</CardDescription>
                        </CardHeader>
                        <CardContent className="flex flex-grow flex-col items-start justify-start p-6">

                            <div className="w-full aspect-video mb-4 overflow-hidden rounded-md border bg-muted">
                                <img src={productData.image} alt={productData.name} className="h-full w-full object-contain" />
                            </div>
                            <p className={'text-base text-foreground mb-4'}>
                                {productData.description}
                            </p>

                            <div className="mt-auto w-full text-right">
                                {activePurchasePool && currentDiscountPercent > 0 ? (
                                    <>
                                        <p className="text-lg font-semibold text-muted-foreground line-through">
                                            ${pricePerUnit.toFixed(2)} / {productData.unit}
                                        </p>
                                        <p className="text-2xl font-bold text-primary">
                                            ${discountedPricePerUnit.toFixed(2)} / {productData.unit}
                                            <Badge variant="destructive" className="ml-2 align-middle">-{currentDiscountPercent}%</Badge>
                                        </p>
                                    </>
                                ) : (
                                    <p className="text-xl lg:text-2xl font-semibold text-foreground">
                                        ${pricePerUnit.toFixed(2)} / {productData.unit}
                                    </p>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Order/Pool Info Card (Right) */}
                    <Card className="flex h-full flex-col justify-between rounded-lg shadow-md md:order-2">
                        <CardHeader>
                            <CardTitle>Order {activePurchasePool && '& Purchase Pool'}</CardTitle> {/* Adjust title based on pool presence */}
                            <CardDescription>
                                {activePurchasePool ? 'Join the active purchase pool below to get a discount on your order of ' : 'Order '}
                                {productData.name}.
                            </CardDescription>
                            {activePurchasePool && (
                                <div className="mt-4 p-3 bg-primary text-primary-foreground rounded-md text-center font-bold">
                                    <p className="text-lg">Pool Closes In:</p>
                                    <p className="text-2xl">
                                        <CountdownTimer endDate={activePurchasePool.end_date!} />
                                    </p>
                                </div>
                            )}
                        </CardHeader>
                        <CardContent className="flex-grow space-y-4">
                            {activePurchasePool ? renderPurchasePoolInfo() : renderPurchasePoolInfo()} {/* Render pool info always, it handles the no-pool case */}

                            {activePurchasePool && <Separator className='my-6' />}

                            {/* Conditional rendering of the form or the "already ordered" message */}
                            {activePurchasePool && hasOrder ? (
                                // Display message if user has already ordered in the active pool
                                <div className={'flex flex-col items-center justify-start gap-2 text-center p-4 border rounded-md bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'}>
                                    <CheckCircle className="inline h-6 w-6 mb-2" />
                                    <p className={'text-sm font-medium'}>
                                        You have already placed an order for this product in the active purchase pool.
                                    </p>
                                    {/* Optional: Link to view their order */}
                                    <Link href={route('orders.index')} className={'text-sm link mt-1'}>View My Orders</Link>
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
                                        {errors.quantity && <p className="text-sm text-red-500 mt-1">{errors.quantity}</p>}
                                    </div>

                                    <div className="grid grid-cols-1 gap-2">
                                        <Label htmlFor="expected_delivery_date">Your Preferred Delivery Date (Optional)</Label>
                                        <Input
                                            type="date"
                                            id="expected_delivery_date"
                                            name="expected_delivery_date"
                                            className="col-span-3 text-foreground"
                                            onChange={(e) => setData('expected_delivery_date', e.target.value)}
                                            value={formdata.expected_delivery_date}
                                            min={new Date().toISOString().split('T')[0]}
                                            disabled={processing}
                                        />
                                        {errors.expected_delivery_date && <p className="text-sm text-red-500 mt-1">{errors.expected_delivery_date}</p>}
                                    </div>

                                    <Separator />

                                    <div className="flex items-center justify-between">
                                        <p className="font-semibold">Order Total</p>
                                        <span className="text-xl font-bold text-foreground">
                                            ${total.toFixed(2)}
                                            {activePurchasePool && currentDiscountPercent > 0 && (
                                                <Badge variant="secondary" className="ml-2 align-middle text-primary">{currentDiscountPercent}% off applied</Badge>
                                            )}
                                        </span>
                                    </div>

                                    {/* Hidden fields (already in useForm) */}
                                    <input type="hidden" name="product_id" value={productData.id} />
                                    {/* Ensure purchase_pool_id is only sent if activePurchasePool exists */}
                                    {activePurchasePool && <input type="hidden" name="purchase_pool_pool_id" value={activePurchasePool.id} />} {/* Corrected name */}


                                    <div className="mt-auto pt-4">
                                        {getFormButton()} {/* This will render the submit button if applicable */}
                                    </div>
                                </form>
                            )}


                        </CardContent>
                    </Card>
                </div>
            </div>
        </LandingLayout>
    );
}
