import { useEffect, useState } from 'react';
import { useForm, usePage } from '@inertiajs/react';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Mail } from 'lucide-react';

export default function EmailVerificationNotice( ) {
    const { auth, flash } = usePage().props;

    const { post } = useForm();

    const [showSuccess, setShowSuccess] = useState(false);

    const resendVerificationEmail = () => {
        post(route('verification.send'), {
            onSuccess: () => setShowSuccess(true),
        });
    };

    useEffect(() => {
        if (flash?.message) {
            setShowSuccess(true);
            const timer = setTimeout(() => {
                setShowSuccess(false);
            }, 3000);
            return () => clearTimeout(timer);
        }
    }, [flash?.message]);

    if (auth.user?.email_verified_at) {
        return null; // Don't show the notice if the email is verified
    }

    return (
        <>
            <Alert className={'mt-4 text-orange-500'}>
                <Mail className="h-4 w-4" />
                <AlertTitle>Your email is not verified.</AlertTitle>
                <AlertDescription>
                    Please verify your email address to access all features.
                </AlertDescription>
                <Button variant="outline" size="sm" onClick={resendVerificationEmail}>
                    Resend Verification Email
                </Button>
            </Alert>

            {showSuccess && (
                <Alert className="mt-4 text-green-500">
                    <Mail className="h-4 w-4" />
                    <AlertTitle>Success</AlertTitle>
                    <AlertDescription>{flash?.message || 'Verification link sent!'}</AlertDescription>
                </Alert>
            )}
        </>
    );
}
