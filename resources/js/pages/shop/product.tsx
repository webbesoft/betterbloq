import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Product } from '@/types/model-types';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler, useEffect, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Market',
        href: '/market',
    },
];

interface ProductData {
    data: Product;
}

interface ProductProps {
    product: ProductData;
}

type OrderForm = {
    product_id: number;
    quantity: number;
    expected_delivery_date: string;
};

export default function Market(props: ProductProps) {
    const { product } = props;

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
        quantity: 0,
        expected_delivery_date: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('buy-product.store'), {
            onFinish: () => reset('quantity', 'expected_delivery_date'),
            onError: () => reset('quantity', 'expected_delivery_date'),
        });
    };

    const [total, setTotal] = useState(0);

    useEffect(() => {
        setTotal(formdata.quantity * data.price);
    }, [data.price, formdata]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={data.name} />
            <div className="flex h-full w-full items-start justify-between gap-2 rounded-xl p-4">
                <div className="flex w-1/2 flex-col gap-2 rounded-md p-4" key={data.id}>
                    <h2 className="h2-text">{data.vendor.name}</h2>
                    <img src={data.image} alt={data.name} />
                    <p className="text">{data.name}</p>
                    <span>
                        ${data.price}/{data.unit}
                    </span>
                </div>
                <div className="flex w-1/2 flex-col gap-2 p-4">
                    <form className="flex flex-col gap-4" onSubmit={submit}>
                        {errors.quantity && <p className="error-text">{errors.quantity}</p>}
                        <Label className="form-with-label" htmlFor="quantity">
                            Quantity
                            <Input
                                type="number"
                                id="quantity"
                                name="quantity"
                                className="border px-2 py-1"
                                onChange={(e) => setData('quantity', Number(e.target.value))}
                                value={formdata.quantity}
                            />
                        </Label>
                        {errors.expected_delivery_date && <p className="error-text">{errors.expected_delivery_date}</p>}
                        <Label className="form-with-label" htmlFor="expected_delivery_date">
                            Expected Delivery Date
                            <Input
                                type="date"
                                id="expected_delivery_date"
                                name="expected_delivery_date"
                                className="border px-2 py-1"
                                onChange={(e) => setData('expected_delivery_date', e.target.value)}
                                value={formdata.expected_delivery_date}
                            />
                        </Label>
                        <Separator />
                        <div className="flex items-center justify-between">
                            <p>Total </p>
                            <span>${total}</span>
                        </div>

                        <input type="hidden" name="product_id" value={data.id} />
                        <button type="submit" className="button primary-button text-center" disabled={processing}>
                            Order
                        </button>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
