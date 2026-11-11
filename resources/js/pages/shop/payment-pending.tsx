import { Brand } from '@/components/brand';
import { Button } from '@/components/ui/button';
import { Head } from '@inertiajs/react';
import { ArrowRight, Loader2, LockKeyhole } from 'lucide-react';
import { useEffect } from 'react';

export default function PaymentPending({ url }: { url: string }) {
    useEffect(() => {
        window.location.href = url || route('orders.index');
    }, [url]);

    return (
        <>
            <Head title="Opening secure checkout" />
            <main className="flex min-h-svh items-center justify-center p-6">
                <div className="surface w-full max-w-md p-8 text-center sm:p-10">
                    <Brand compact />
                    <div className="bg-secondary mx-auto mt-8 mb-6 flex size-16 items-center justify-center rounded-2xl">
                        <Loader2 className="text-primary size-6 animate-spin" />
                    </div>
                    <h1 className="text-2xl font-semibold tracking-tight">You’re on your way.</h1>
                    <p className="text-muted-foreground mt-3 text-sm leading-6" role="status">
                        Opening your secure checkout. You’ll review the final total before you pay.
                    </p>
                    <Button asChild className="mt-7 h-11 w-full rounded-xl">
                        <a href={url || route('orders.index')}>
                            Continue
                            <ArrowRight className="size-4" />
                        </a>
                    </Button>
                    <p className="text-muted-foreground mt-5 flex items-center justify-center gap-1.5 text-[10px]">
                        <LockKeyhole className="size-3" />
                        Payments processed securely by Stripe
                    </p>
                </div>
            </main>
        </>
    );
}
