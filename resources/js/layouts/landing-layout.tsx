import { AppShell } from '@/components/app-shell';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { LandingFooter } from '@/components/landing-footer';
import { LandingNavigation } from '@/components/landing-navigation';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { NavItem, type BreadcrumbItem } from '@/types';
import { usePage } from '@inertiajs/react';
import { AlertCircle, Check, Home, InfoIcon } from 'lucide-react';
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
        url: route('market')
    }
];

export default ({ children, breadcrumbs }: LandingLayoutProps) => {
    const { flash } = usePage<{flash: {
                message: {
                    success?: string;
                    error?: string;
                    message?: string;
                }
            }}>().props;
    
    return (
    <AppShell>
        <LandingNavigation items={navItems} />
        <div className='container mx-auto'>
            <header className="border-sidebar-border/50 flex h-16 shrink-0 items-center gap-4 border-b px-4 sm:px-6 md:px-4 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12">
                {!['/', '/plans'].includes(window.location.pathname) &&
                    <div className="flex items-center gap-2">
                    <Home className="h-4 w-4 text-foreground" />
                    <h1 className="text-lg font-semibold text-foreground truncate">
                        {breadcrumbs && breadcrumbs.length > 0 ? breadcrumbs[breadcrumbs.length - 1].title : 'Default Title'}
                    </h1>
                </div>
                }
                {breadcrumbs && breadcrumbs.length > 0 && (
                    <div className="flex items-center gap-2 text-sm text-muted-foreground overflow-x-auto scrollbar-hide">
                        <Breadcrumbs breadcrumbs={breadcrumbs} />
                    </div>
                )}
            </header>
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

            </div>
            {children}
        <LandingFooter />
    </AppShell>
)};
