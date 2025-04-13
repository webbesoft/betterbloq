import { SharedData, type NavItem } from '@/types';
import { Link, router, usePage } from '@inertiajs/react';
import { LayoutDashboard, LogOut, User } from 'lucide-react';
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
        // Use Inertia.post for logout
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
                        <img
                            src="/images/betterbloq.png"
                            alt="betterbloq logo"
                            className="h-8 w-auto" // Adjusted size
                        />
                        <span className="text-foreground hidden font-bold sm:inline-block">BetterBloq</span> {/* Hide text on xs screens */}
                    </Link>

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
                                <DropdownMenuContent className="w-56" align="end" forceMount>
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
                                        {/* Adjust route name 'profile.edit' if needed */}
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
                                <Button asChild variant="ghost" size="sm">
                                    <Link href={route('login')} className="stretch text">
                                        <span className={'text-foreground'}>
                                            Log in
                                        </span>
                                    </Link>
                                </Button>
                                <Button asChild variant="default" size="sm">
                                    <Link href={route('register')}>Register</Link>
                                </Button>
                            </>
                        )}

                        {/* Mobile Menu (Add Sheet component here if needed) */}
                        {/* <div className="lg:hidden"> ... Sheet Trigger ... </div> */}
                    </div>
                </nav>
            </header>
        </div>
    );
}
