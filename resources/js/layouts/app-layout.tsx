import AppLayoutTemplate from '@/layouts/app/app-sidebar-layout';
import { type BreadcrumbItem } from '@/types';
import { usePage } from '@inertiajs/react';
import { type ReactNode } from 'react';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { AlertCircle, Check, InfoIcon } from 'lucide-react';

interface AppLayoutProps {
    children: ReactNode;
    breadcrumbs?: BreadcrumbItem[];
}

export default ({ children, breadcrumbs, ...props }: AppLayoutProps) => {
    const { flash } = usePage<{flash: {
            message: {
                success?: string;
                error?: string;
                message?: string;
            }
        }}>().props;

    return (
        <AppLayoutTemplate breadcrumbs={breadcrumbs} {...props}>
            <div className={'m-2'}>
            {(() => {
                if (flash) {
                    if (flash.message?.error) {
                        return (
                            <Alert variant={'destructive'}>
                                <AlertCircle />
                                <AlertTitle>
                                    Error
                                </AlertTitle>
                                <AlertDescription>
                                    {flash.message.error}
                                </AlertDescription>
                            </Alert>
                        )
                    } else if (flash.message?.success) {
                        return (
                            <Alert variant={'default'} className={'text-green-500/80'}>
                                <Check />
                                <AlertTitle>
                                    Success
                                </AlertTitle>
                                <AlertDescription className={'text-green-500/80'}>
                                    {flash.message.success}
                                </AlertDescription>
                            </Alert>
                        )
                    } else if (flash.message?.message) {
                        return (
                            <Alert variant={'default'} className={'text-blue-500/80'}>
                                <InfoIcon />
                                <AlertTitle>
                                    Info
                                </AlertTitle>
                                <AlertDescription className={'text-blue-500/80'}>
                                    {flash.message.message}
                                </AlertDescription>
                            </Alert>
                            )
                    }
                    return null;
                }
            })()}
            </div>

            {children}
        </AppLayoutTemplate>
    );
};
