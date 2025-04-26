import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { BookOpen, Box, House, HousePlus, LayoutGrid, ShoppingBag, TargetIcon, TruckIcon } from 'lucide-react';
import AppLogo from './app-logo';

interface UserData {
    id: number;
    name: string;
    email: string;
    plan_slug?: string | null;
}

// interface SharedProps {
//     auth: {
//         user: UserData | null;
//     };
// }

const mainNavItems: NavItem[] = [
    {
        title: 'Home',
        url: '/',
        icon: House,
        allowedPlans: []
    },
    {
        title: 'Dashboard',
        url: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Projects',
        url: '/projects',
        icon: HousePlus,
        allowedPlans: ['pro', 'basic'],
    },
    {
        title: 'Purchase Pools',
        url: '/purchase-pools',
        icon: TargetIcon,
        allowedPlans: [],
    },
    {
        title: 'Orders',
        url: route('orders.index'),
        icon: TruckIcon,
        allowedPlans: ['pro', 'free'],
    },
    {
        title: 'Market',
        url: route('market'),
        icon: ShoppingBag,
        allowedPlans: ['pro', 'free', 'basic'],
    },
    {
        title: 'Upgrade Plan',
        url: '/shop/plans',
        icon: ShoppingBag,
        allowedPlans: [],
    }
];

const footerNavItems: NavItem[] = [
    // {
    //     title: 'Documentation',
    //     url: route('landing'),
    //     icon: BookOpen,
    // },
];

export function AppSidebar() {
    const { props } = usePage<any>();
    const currentUser = props.auth?.user;

    const userPlanSlug = currentUser?.plan_slug || 'free';

    
    const filteredMainNavItems = mainNavItems.filter(item => {
    
        if (!item.allowedPlans) {
            return true;
        }
    
        if (!currentUser) {
             return false;
        }
    
        return item.allowedPlans.includes(userPlanSlug);
    });

    
     const filteredFooterNavItems = footerNavItems.filter(item => {
        if (!item.allowedPlans) {
            return true; 
        }
         if (!currentUser) {
             return false; 
         }
        return item.allowedPlans.includes(userPlanSlug);
    });

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/dashboard" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={filteredMainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
