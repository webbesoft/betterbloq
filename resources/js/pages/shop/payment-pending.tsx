import { Head } from '@inertiajs/react';
import { Loader } from 'lucide-react';
import { useEffect } from 'react';

export default function PaymentPending(props: { url: string }) {
    console.log(props);
    const { url } = props;

    useEffect(() => {
        if (url) {
            window.location.href = url;
        } else {
            window.location.href = route('orders.index');
        }
    }, [url]);

    return (
        <>
            {/* <LandingLayout breadcrumbs={[]}> */}
            <Head title={'Payment Pending'} />
            <div className="flex h-screen items-center justify-center">
                <Loader />
            </div>
            {/* </LandingLayout> */}
        </>
    );
}
