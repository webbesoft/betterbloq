import { SharedData, type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';

export function LandingNavigation({ items = [] }: { items: NavItem[] }) {
    const { auth } = usePage<SharedData>().props;

    return (
        <header className="mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-4xl p-8">
            <nav className="flex items-center justify-between gap-4">
                <div className='float-left rounded-md'>
                    <img src='/images/betterbloq.png' alt='betterbloq' height={'60px'} width={'90px'} />
                </div>
                <div>
                    {items.map((item) => (
                        <Link
                            href={item.url}
                            className="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                            key={item.title}
                            style={{ display: 'none' }}
                        >
                            {item.title}
                        </Link>
                    ))}
                </div>
                <div className='nav-element float-right'>
                {auth.user ? (
                    <Link
                        href={route('buy-dashboard')}
                        className="inline-block rounded-sm px-5 py-1.5 text-sm leading-normal text-[var(--custom-text)]"
                    >
                        Dashboard
                    </Link>
                ) : (
                    <div className='flex items-center justify-center'>
                        <Link
                            href={route('login')}
                            className="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[var(--custom-text)]"
                        >
                            Log in
                        </Link>
                        <Link
                            href={route('register')}
                            className="inline-block rounded-sm px-5 py-1.5 text-sm leading-normal text-[var(--custom-text)]"
                        >
                            Register
                        </Link>
                    </div>
                )}
                </div>
            </nav>
        </header>
    );
}
