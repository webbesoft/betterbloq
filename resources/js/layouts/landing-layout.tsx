import { AppShell } from '@/components/app-shell';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { LandingFooter } from '@/components/landing-footer';
import { LandingNavigation } from '@/components/landing-navigation';
import { NavItem, type BreadcrumbItem } from '@/types';
import { usePage } from '@inertiajs/react';
import { Home } from 'lucide-react';
import { type ReactNode } from 'react';

interface LandingLayoutProps {
    children: ReactNode;
    breadcrumbs: BreadcrumbItem[];
}

const navItems: NavItem[] = [
    {
        title: 'Home',
        url: route('landing'),
    },
    {
        title: 'Market',
        url: route('market'),
    },
    {
        title: 'Categories',
        url: route('categories.index'),
    },
];

export default ({ children, breadcrumbs }: LandingLayoutProps) => {
    const { flash } = usePage<{
        flash: {
            message: {
                success?: string;
                error?: string;
                message?: string;
            };
        };
    }>().props;

    return (
        <AppShell>
            <LandingNavigation items={navItems} />
            <div className="container mx-auto">
                <header className="border-sidebar-border/50 flex h-16 shrink-0 items-center gap-4 border-b px-4 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 sm:px-6 md:px-4">
                    {!['/', '/plans'].includes(window.location.pathname) && (
                        <div className="flex items-center gap-2">
                            <Home className="text-foreground h-4 w-4" />
                            <h1 className="text-foreground truncate text-lg font-semibold">
                                {breadcrumbs && breadcrumbs.length > 0 ? breadcrumbs[breadcrumbs.length - 1].title : 'Default Title'}
                            </h1>
                        </div>
                    )}
                    {breadcrumbs && breadcrumbs.length > 0 && (
                        <div className="text-muted-foreground scrollbar-hide flex items-center gap-2 overflow-x-auto text-sm">
                            <Breadcrumbs breadcrumbs={breadcrumbs} />
                        </div>
                    )}
                </header>
                <div className={'m-2'}>
                    {/* Flash Messages */}
                    {flash.message && (flash.message.success || flash.message.error || flash.message.message) && (
                        <div
                            className={`mb-6 rounded-md p-4 text-sm font-medium ${
                                flash.message.success
                                    ? 'border border-green-300 bg-green-100 text-green-700 dark:border-green-700 dark:bg-green-900/30 dark:text-green-300'
                                    : flash.message.error
                                      ? 'border border-red-300 bg-red-100 text-red-700 dark:border-red-700 dark:bg-red-900/30 dark:text-red-300'
                                      : 'border border-blue-300 bg-blue-100 text-blue-700 dark:border-blue-700 dark:bg-blue-900/30 dark:text-blue-300'
                            }`}
                            role="alert"
                        >
                            {flash.message.success || flash.message.error || flash.message.message}
                        </div>
                    )}
                </div>
            </div>
            {children}
            <LandingFooter />
        </AppShell>
    );
};
