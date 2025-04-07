import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Product } from '@/types/model-types';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler, useEffect, useState } from 'react';
import { Clock } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Product',
        href: '/market',
    },
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
}

type OrderForm = {
    product_id: number;
    quantity: number;
    expected_delivery_date: string;
};

export default function Market(props: ProductProps) {
    const { product, flash, hasPurchasePoolRequest } = props;

    const { data } = product;

    const {
        data: formdata,
        setData,
        post,
        processing,
        errors,
        reset,
    } = useForm<OrderForm>({
        product_id: data.id,
        quantity: 1,
        expected_delivery_date: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        if (flash?.message?.error) {
            post(route('purchase-pool-requests.store'), {
                onFinish: () => {}
            });

            return;
        }

        post(route('orders.store'), {
            onFinish: () => {},
            onError: () => {},
            onSuccess: () => reset('quantity', 'expected_delivery_date')
        });
    };

    const [total, setTotal] = useState(data.price);

    const getFormButton = () => {
        if (hasPurchasePoolRequest) {
            return (
                <div className={'flex flex-col items-center justify-start gap-2'}>
                    <p className={'text-sm font-light text-foreground/60'}>
                        You have a pending purchase pool request for this product.
                        {' '}<Link href={'#'} className={'link'}>View Requests</Link>
                    </p>
                </div>
            )
        }

        if (flash?.message?.error) {
            return (
                <Button type="submit" className="mt-auto w-full" disabled={processing}>
                    <Clock />
                    Request Purchase Pool
                </Button>
            )
        }

        return (
            <Button type="submit" className="mt-auto w-full" disabled={processing}>
                {processing ? 'Ordering...' : 'Order Now'}
            </Button>
        )
    }

    useEffect(() => {
        setTotal(formdata.quantity * data.price);
    }, [data.price, formdata]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={data.name} />
            <div className="container mx-auto h-full py-8">
                <div className="grid h-full grid-cols-1 gap-6 md:grid-cols-2">
                    <Card className="flex h-full flex-col justify-between rounded-xs shadow-sm md:order-1">
                        {' '}
                        <CardHeader>
                            <CardTitle className="text-xl font-semibold">{data.vendor.name}</CardTitle>
                            <CardDescription className="text-gray-500">{data.name}</CardDescription>
                        </CardHeader>
                        <CardContent className="flex flex-grow flex-col items-start justify-start p-6">
                            <div className="aspect-w-1 aspect-h-1 mb-4 w-full max-w-md overflow-hidden rounded-md">
                                <img src={data.image} alt={data.name} className="h-full max-h-[300px] w-full object-cover" />
                            </div>
                            <p className={'text-md text-foreground mb-2'}>
                                {data.description}
                            </p>
                            <p className="text-lg font-semibold text-foreground text-center">
                                ${data.price} / {data.unit}
                            </p>
                        </CardContent>
                        <CardFooter></CardFooter>
                    </Card>

                    <Card className="flex h-full flex-col justify-between rounded-xs shadow-sm md:order-2">
                        {' '}
                        {/* Ensure order form is second on larger screens */}
                        <CardHeader>
                            <CardTitle>Order Information</CardTitle>
                            <CardDescription>Place your order for {data.name}</CardDescription>
                        </CardHeader>
                        <CardContent className="flex-grow space-y-4">
                            <form className="flex h-full flex-col justify-start space-y-4" onSubmit={submit}>
                                {errors.quantity && <p className="text-sm text-red-500">{errors.quantity}</p>}
                                <div className="grid grid-cols-1 gap-2">
                                    <Label htmlFor="quantity">Quantity</Label>
                                    <Input
                                        type="number"
                                        id="quantity"
                                        name="quantity"
                                        className="col-span-3"
                                        onChange={(e) => setData('quantity', Number(e.target.value))}
                                        value={formdata.quantity}
                                        min="1"
                                    />
                                </div>

                                {errors.expected_delivery_date && <p className="text-sm text-red-500">{errors.expected_delivery_date}</p>}
                                <div className="grid grid-cols-1 gap-2">
                                    <Label htmlFor="expected_delivery_date">Expected Delivery Date</Label>
                                    <Input
                                        type="date"
                                        id="expected_delivery_date"
                                        name="expected_delivery_date"
                                        className="col-span-3 text-foreground"
                                        onChange={(e) => setData('expected_delivery_date', e.target.value)}
                                        value={formdata.expected_delivery_date}
                                    />
                                </div>

                                <Separator />

                                <div className="flex items-center justify-between">
                                    <p className="font-semibold">Total</p>
                                    <span className="text-xl font-bold text-foreground">${total}</span>
                                </div>

                                <input type="hidden" name="product_id" value={data.id} />
                                {getFormButton()}

                            </form>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
