import { Link } from '@inertiajs/react';
import { ArrowUpRight } from 'lucide-react';
import { Brand } from './brand';

export function LandingFooter() {
    return (
        <footer className="bg-secondary/45 mt-20 border-t sm:mt-28">
            <div className="shop-container grid gap-10 py-12 sm:grid-cols-[1.5fr_1fr_1fr] sm:py-16">
                <div>
                    <Link href="/" aria-label="BetterBloq home">
                        <Brand />
                    </Link>
                    <p className="text-muted-foreground mt-4 max-w-64 text-sm leading-6">
                        Better materials. Shared buying power.
                        <br />A solid start for your next project.
                    </p>
                </div>
                <div>
                    <h2 className="mb-4 text-xs font-semibold tracking-widest uppercase">Start building</h2>
                    <div className="text-muted-foreground grid gap-3 text-sm">
                        <Link href={route('market')} className="hover:text-primary w-fit">
                            Explore the market
                        </Link>
                        <Link href={route('categories.index')} className="hover:text-primary w-fit">
                            Browse categories
                        </Link>
                        <Link href="/#how-it-works" className="hover:text-primary w-fit">
                            How it works
                        </Link>
                    </div>
                </div>
                <div>
                    <h2 className="mb-4 text-xs font-semibold tracking-widest uppercase">Your workspace</h2>
                    <div className="text-muted-foreground grid gap-3 text-sm">
                        <Link href={route('plans.index')} className="hover:text-primary w-fit">
                            Plans & membership
                        </Link>
                        <Link href={route('login')} className="hover:text-primary w-fit">
                            Log in
                        </Link>
                        <Link href={route('register')} className="text-primary flex w-fit items-center gap-1.5 font-medium">
                            Join BetterBloq <ArrowUpRight className="size-3.5" />
                        </Link>
                    </div>
                </div>
            </div>
            <div className="shop-container text-muted-foreground flex flex-col justify-between gap-2 border-t py-5 text-xs sm:flex-row">
                <p>© {new Date().getFullYear()} BetterBloq. All rights reserved.</p>
                <p>Let’s build better, together.</p>
            </div>
        </footer>
    );
}
