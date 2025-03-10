import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Market',
        href: '/market',
    },
];

export default function Market() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Market" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <p>Hello Buyer!</p>
            </div>
        </AppLayout>
    );
}
