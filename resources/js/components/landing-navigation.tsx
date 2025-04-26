import { SharedData, type NavItem } from '@/types';
import { Link, router, usePage } from '@inertiajs/react';
import { LayoutDashboard, LogOut, Menu, User } from 'lucide-react';
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
import { NavigationMenu, NavigationMenuItem, NavigationMenuLink, NavigationMenuList, navigationMenuTriggerStyle } from './ui/navigation-menu';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from './ui/sheet';

// Helper to get initials from name
const getInitials = (name = '') => {
    return name
        .split(' ')
        .map((n) => n[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
};

export function LandingNavigation({ items = [] }: { items: NavItem[] }) {
    const { auth } = usePage<SharedData>().props;
    const { user } = auth;

    const handleLogout = (e: React.MouseEvent<HTMLDivElement, MouseEvent>): void => {
        e.preventDefault();
        router.post(route('logout'));
    };

    return (
        <div className="container mx-auto border-b px-4 sm:px-6 lg:px-8 z-100">
            {' '}
            <header className="w-full py-3">
                {' '}
                <nav className="flex h-14 items-center justify-between gap-6">
                    {/* Logo */}
                    <Link href="/" className="mr-4 flex items-center gap-2">
                        {' '}
                        <div className='rounded-md'>
                            <img
                                src="/images/3.png"
                                alt="betterbloq logo"
                                className="h-10 w-auto bg-white"
                            />
                        </div>
                        <span className="text-foreground hidden font-bold sm:inline-block">BetterBloq</span>
                    </Link>

                    {/* Desktop Navigation */}
                    {items && items.length > 0 && (
                        <NavigationMenu className="hidden flex-1 lg:flex">
                            {' '}
                            <NavigationMenuList>
                                {items.map((item) => (
                                    <NavigationMenuItem key={item.title}>
                                        <NavigationMenuLink className={navigationMenuTriggerStyle()} href={item.url}>{item.title}</NavigationMenuLink>
                                    </NavigationMenuItem>
                                ))}
                            </NavigationMenuList>
                        </NavigationMenu>
                    )}

                    {/* Auth Area */}
                    <div className="flex items-center gap-3">
                        {user ? (
                            <DropdownMenu>
                                <DropdownMenuTrigger asChild>
                                    <Button variant="ghost" className="relative h-8 w-8 rounded-full">
                                        <Avatar className="h-8 w-8">
                                            {user.avatar && <AvatarImage src={user.avatar} alt={user.name} />}
                                            <AvatarFallback>{getInitials(user.name)}</AvatarFallback>
                                        </Avatar>
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent className="w-56 z-100" align="end" forceMount>
                                    <DropdownMenuLabel className="font-normal">
                                        <div className="flex flex-col space-y-1">
                                            <p className="text-sm leading-none font-medium">{user.name}</p>
                                            {user.email && <p className="text-muted-foreground text-xs leading-none">{user.email}</p>}
                                        </div>
                                    </DropdownMenuLabel>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem asChild>
                                        <Link href={route('dashboard')}>
                                            <LayoutDashboard className="mr-2 h-4 w-4" />
                                            <span>Dashboard</span>
                                        </Link>
                                    </DropdownMenuItem>
                                    <DropdownMenuItem asChild>
                                        <Link href={route('profile.edit')}>
                                            <User className="mr-2 h-4 w-4" />
                                            <span>Profile</span>
                                        </Link>
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    {/* Logout needs special handling */}
                                    <DropdownMenuItem onClick={handleLogout} className="cursor-pointer">
                                        <LogOut className="mr-2 h-4 w-4" />
                                        <span>Log out</span>
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        ) : (
                            <>
                                <Button variant="ghost" size="sm" className="hidden sm:inline-flex"> {/* Hide on extra-small screens */}
                                    <Link href={route('login')} className="stretch text">
                                        <span className={'text-foreground'}>
                                            Log in
                                        </span>
                                    </Link>
                                </Button>
                                <Button variant="default" size="sm" className="hidden sm:inline-flex"> {/* Hide on extra-small screens */}
                                    <Link href={route('register')}>Register</Link>
                                </Button>
                                {/* Mobile Login/Register Buttons */}
                                <div className="flex gap-2 sm:hidden">
                                    <Button variant="ghost" size="sm">
                                        <Link href={route('login')} className="stretch text">
                                            <span className={'text-foreground'}>
                                                Log in
                                            </span>
                                        </Link>
                                    </Button>
                                    <Button variant="default" size="sm">
                                        <Link href={route('register')}>Register</Link>
                                    </Button>
                                </div>
                            </>
                        )}

                        {/* Mobile Menu */}
                        {items && items.length > 0 && (
                            <div className="lg:hidden">
                                <Sheet>
                                    <SheetTrigger asChild>
                                        <Button variant="outline" size="icon">
                                            <Menu className="h-4 w-4" />
                                        </Button>
                                    </SheetTrigger>
                                    <SheetContent className="pr-4 sm:pr-6 lg:pr-8 bg-background">
                                        <SheetHeader>
                                            <SheetTitle>Menu</SheetTitle>
                                        </SheetHeader>
                                        <div className="grid gap-4 py-4">
                                            {items.map((item) => (
                                                <Link key={item.title} href={item.url} className="hover:underline">
                                                    {item.title}
                                                </Link>
                                            ))}
                                            {user && (
                                                <>
                                                    <DropdownMenu>
                                                        <DropdownMenuTrigger asChild>
                                                            <Button variant="ghost" className="relative w-full justify-start rounded-md">
                                                                <Avatar className="mr-2 h-6 w-6">
                                                                    {user.avatar && <AvatarImage src={user.avatar} alt={user.name} />}
                                                                    <AvatarFallback>{getInitials(user.name)}</AvatarFallback>
                                                                </Avatar>
                                                                <span>{user.name || 'User'}</span>
                                                            </Button>
                                                        </DropdownMenuTrigger>
                                                        <DropdownMenuContent className="w-56" align="start" forceMount>
                                                            <DropdownMenuItem>
                                                                <Link href={route('dashboard')}>
                                                                    <LayoutDashboard className="mr-2 h-4 w-4" />
                                                                    <span>Dashboard</span>
                                                                </Link>
                                                            </DropdownMenuItem>
                                                            <DropdownMenuItem>
                                                                <Link href={route('profile.edit')}>
                                                                    <User className="mr-2 h-4 w-4" />
                                                                    <span>Profile</span>
                                                                </Link>
                                                            </DropdownMenuItem>
                                                            <DropdownMenuSeparator />
                                                            <DropdownMenuItem onClick={handleLogout} className="cursor-pointer">
                                                                <LogOut className="mr-2 h-4 w-4" />
                                                                <span>Log out</span>
                                                            </DropdownMenuItem>
                                                        </DropdownMenuContent>
                                                    </DropdownMenu>
                                                </>
                                            )}
                                        </div>
                                    </SheetContent>
                                </Sheet>
                            </div>
                        )}
                    </div>
                </nav>
            </header>
        </div>
    );
}
