import { Breadcrumbs } from '@/components/breadcrumbs';
import { LandingFooter } from '@/components/landing-footer';
import { LandingNavigation } from '@/components/landing-navigation';
import { NavItem, type BreadcrumbItem } from '@/types';
import { usePage } from '@inertiajs/react';
import { CircleAlert, CircleCheck, Info } from 'lucide-react';
import { type ReactNode } from 'react';
import { Toaster } from 'sonner';

interface LandingLayoutProps {
    children: ReactNode;
    breadcrumbs: BreadcrumbItem[];
}

const navItems: NavItem[] = [
    { title: 'Home', url: '/' },
    { title: 'Market', url: '/market' },
    { title: 'Categories', url: '/categories' },
];

export default function LandingLayout({ children, breadcrumbs }: LandingLayoutProps) {
    const { flash } = usePage<{ flash?: { message?: { success?: string; error?: string; message?: string } } }>().props;
    const message = flash?.message;
    const Icon = message?.error ? CircleAlert : message?.success ? CircleCheck : Info;

    return (
        <div className="flex min-h-screen flex-col">
            <a
                href="#main-content"
                className="bg-foreground text-background fixed top-2 left-2 z-[100] -translate-y-24 rounded-lg px-4 py-3 text-sm focus:translate-y-0"
            >
                Skip to content
            </a>
            <LandingNavigation items={navItems} />
            {breadcrumbs.length > 0 && (
                <div className="shop-container pt-6 text-xs sm:pt-8">
                    <Breadcrumbs breadcrumbs={breadcrumbs[0]?.href === '/' ? breadcrumbs : [{ title: 'Home', href: '/' }, ...breadcrumbs]} />
                </div>
            )}
            {message && (message.success || message.error || message.message) && (
                <div className="shop-container mt-5">
                    <div
                        className={`flex items-start gap-3 rounded-xl border p-4 text-sm ${message.error ? 'border-destructive/25 bg-destructive/5 text-destructive' : 'bg-secondary text-secondary-foreground'}`}
                        role="alert"
                    >
                        <Icon className="mt-0.5 size-4 shrink-0" />
                        {message.success || message.error || message.message}
                    </div>
                </div>
            )}
            <main id="main-content" className="min-w-0 flex-1" tabIndex={-1}>
                {children}
            </main>
            <LandingFooter />
            <Toaster closeButton richColors duration={3000} />
        </div>
    );
}
