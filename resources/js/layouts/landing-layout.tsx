import { AppShell } from '@/components/app-shell';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { LandingFooter } from '@/components/landing-footer';
import { LandingNavigation } from '@/components/landing-navigation';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { NavItem, type BreadcrumbItem } from '@/types';
import { usePage } from '@inertiajs/react';
import { AlertCircle, Check, InfoIcon } from 'lucide-react';
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
            
            <header className="border-sidebar-border/50 flex h-16 shrink-0 items-center gap-2 border-b px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4">
                <div className="flex items-center gap-2">
                    <Breadcrumbs breadcrumbs={breadcrumbs} />
                </div>
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
