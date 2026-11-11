import { cn } from '@/lib/utils';
import { CartDropdown } from '@/pages/shop/components/cart-dropdown';
import { SharedData, type NavItem } from '@/types';
import { Link, router, usePage } from '@inertiajs/react';
import { ArrowUpRight, LayoutDashboard, LogOut, Menu, User } from 'lucide-react';
import { useState } from 'react';
import { Brand } from './brand';
import { Avatar, AvatarFallback, AvatarImage } from './ui/avatar';
import { Button } from './ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from './ui/dropdown-menu';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from './ui/sheet';

export function LandingNavigation({ items = [] }: { items: NavItem[] }) {
    const { props, url } = usePage<SharedData>();
    const user = props.auth?.user;
    const pathname = url.split('?')[0];
    const [menuOpen, setMenuOpen] = useState(false);
    const active = (href: string) => (href === '/' ? pathname === '/' : pathname.startsWith(href));
    const initials = user?.name
        .split(' ')
        .map((part) => part[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();

    return (
        <header className="bg-background/95 sticky top-0 z-40 border-b backdrop-blur-xl">
            <div className="shop-container flex h-20 items-center justify-between gap-4">
                <Link href="/" aria-label="BetterBloq home" className="shrink-0">
                    <Brand />
                </Link>
                <nav aria-label="Main navigation" className="hidden h-full items-center gap-8 lg:flex">
                    {items.map((item) => (
                        <Link
                            key={item.title}
                            href={item.url}
                            aria-current={active(item.url) ? 'page' : undefined}
                            className={cn(
                                'relative flex h-full items-center text-sm font-medium transition-colors after:absolute after:right-0 after:bottom-0 after:left-0 after:h-0.5 after:rounded-full',
                                active(item.url) ? 'text-foreground after:bg-primary' : 'text-muted-foreground hover:text-foreground',
                            )}
                        >
                            {item.title}
                        </Link>
                    ))}
                </nav>
                <div className="flex items-center gap-2 sm:gap-4">
                    <CartDropdown />
                    <span className="bg-border hidden h-6 w-px sm:block" aria-hidden="true" />
                    {user ? (
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button variant="ghost" className="size-10 rounded-full p-0" aria-label="Open account menu">
                                    <Avatar className="size-9">
                                        {typeof user.avatar === 'string' && <AvatarImage src={user.avatar} alt="" />}
                                        <AvatarFallback className="bg-secondary text-secondary-foreground text-xs font-semibold">
                                            {initials}
                                        </AvatarFallback>
                                    </Avatar>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent className="z-50 w-60 rounded-xl p-2" align="end">
                                <DropdownMenuLabel className="py-3">
                                    <p>{user.name}</p>
                                    <p className="text-muted-foreground mt-1 truncate text-xs font-normal">{user.email}</p>
                                </DropdownMenuLabel>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem asChild>
                                    <Link href={route('dashboard')}>
                                        <LayoutDashboard />
                                        Workspace
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuItem asChild>
                                    <Link href={route('profile.edit')}>
                                        <User />
                                        Profile & settings
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem onSelect={() => router.post(route('logout'))}>
                                    <LogOut />
                                    Log out
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    ) : (
                        <div className="hidden items-center gap-2 sm:flex">
                            <Button variant="ghost" asChild>
                                <Link href={route('login')}>Log in</Link>
                            </Button>
                            <Button asChild className="rounded-xl">
                                <Link href={route('register')}>
                                    Get started <ArrowUpRight className="size-4" />
                                </Link>
                            </Button>
                        </div>
                    )}
                    <Sheet open={menuOpen} onOpenChange={setMenuOpen}>
                        <SheetTrigger asChild>
                            <Button variant="ghost" size="icon" className="lg:hidden" aria-label="Open navigation menu">
                                <Menu className="size-5" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent className="bg-background w-[min(22rem,90vw)] p-6">
                            <SheetHeader className="px-0">
                                <SheetTitle>
                                    <Brand compact />
                                </SheetTitle>
                            </SheetHeader>
                            <nav aria-label="Mobile navigation" className="mt-8 grid gap-2">
                                {items.map((item) => (
                                    <Link
                                        key={item.title}
                                        href={item.url}
                                        onClick={() => setMenuOpen(false)}
                                        aria-current={active(item.url) ? 'page' : undefined}
                                        className={cn(
                                            'rounded-xl px-4 py-3 text-base',
                                            active(item.url) ? 'bg-secondary font-semibold' : 'text-muted-foreground hover:bg-muted',
                                        )}
                                    >
                                        {item.title}
                                    </Link>
                                ))}
                            </nav>
                            <div className="mt-8 grid gap-3 border-t pt-6">
                                <Button asChild>
                                    <Link href={user ? route('dashboard') : route('register')} onClick={() => setMenuOpen(false)}>
                                        {user ? 'Open workspace' : 'Create an account'}
                                        <ArrowUpRight />
                                    </Link>
                                </Button>
                                {!user && (
                                    <Button variant="outline" asChild>
                                        <Link href={route('login')} onClick={() => setMenuOpen(false)}>
                                            Log in
                                        </Link>
                                    </Button>
                                )}
                            </div>
                        </SheetContent>
                    </Sheet>
                </div>
            </div>
        </header>
    );
}
