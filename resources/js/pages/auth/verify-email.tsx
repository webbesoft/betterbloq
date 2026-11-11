// Components
import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler } from 'react';

import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/auth-layout';

export default function VerifyEmail({ status, deliveryMode = 'mail' }: { status?: string; deliveryMode?: 'log' | 'mail' }) {
    const { post, processing } = useForm({});

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('verification.send'));
    };

    return (
        <AuthLayout title="Verify email" description="Please verify your email address by clicking on the link we just emailed to you.">
            <Head title="Email verification" />

            {status === 'verification-link-invalid' && (
                <div role="alert" className="bg-accent text-accent-foreground mb-4 rounded-xl border p-4 text-sm leading-6">
                    This verification link is invalid or has expired. Request a fresh link below, then open the newest message.
                </div>
            )}

            {deliveryMode === 'log' && (
                <p className="text-muted-foreground mb-4 rounded-xl border p-4 text-sm leading-6">
                    Local email delivery is set to log. Find the newest verification message in <code>storage/logs/laravel.log</code> and copy its
                    plain-text URL. Avoid copying the Markdown wrapper or HTML-encoded link.
                </p>
            )}

            {status === 'verification-link-sent' && (
                <div className="mb-4 text-center text-sm font-medium text-green-600">
                    {deliveryMode === 'log'
                        ? 'A new verification link has been written to your local mail log.'
                        : 'A new verification link has been sent to your email address.'}
                </div>
            )}

            <form onSubmit={submit} className="space-y-6 text-center">
                <Button disabled={processing} variant="secondary">
                    {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                    Resend verification email
                </Button>

                <TextLink href={route('logout')} method="post" className="mx-auto block text-sm">
                    Log out
                </TextLink>
            </form>
        </AuthLayout>
    );
}
