import { SharedData, type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';

export function LandingNavigation({ items = [] }: { items: NavItem[] }) {
    const { auth } = usePage<SharedData>().props;

    // const page = usePage();

    return (
        <header className="mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-4xl p-8">
            <nav className="flex items-center justify-between gap-4">
                <div>
                    {items.map((item) => (
                        <Link
                            href={item.url}
                            className="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                            key={item.title}
                        >
                            {item.title}
                        </Link>
                    ))}
                </div>
                {auth.user ? (
                    <Link
                        href={route('buy-dashboard')}
                        className="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                    >
                        Dashboard
                    </Link>
                ) : (
                    <>
                        <Link
                            href={route('login')}
                            className="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                        >
                            Log in
                        </Link>
                        <Link
                            href={route('register')}
                            className="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                        >
                            Register
                        </Link>
                    </>
                )}
            </nav>
        </header>
    );
}
