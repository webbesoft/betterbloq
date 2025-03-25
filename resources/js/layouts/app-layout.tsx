import AppLayoutTemplate from '@/layouts/app/app-sidebar-layout';
import { type BreadcrumbItem } from '@/types';
import { usePage } from '@inertiajs/react';
import { type ReactNode } from 'react';

interface AppLayoutProps {
    children: ReactNode;
    breadcrumbs?: BreadcrumbItem[];
}

export default ({ children, breadcrumbs, ...props }: AppLayoutProps) => {
    const { flash } = usePage().props;

    return (
        <AppLayoutTemplate breadcrumbs={breadcrumbs} {...props}>
            <div className="m-4">{flash.message && <div className="alert">{flash.message.message}</div>}</div>

            {children}
        </AppLayoutTemplate>
    );
};
