import { Button } from '@/components/ui/button';
import LandingLayout from '@/layouts/landing-layout';
import { SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowDown, ArrowRight, ArrowUpRight, Boxes, CalendarDays, Layers3, PackageSearch, ReceiptText, Users } from 'lucide-react';
import BuildingIllustration from './components/landing/building-illustration';

const steps = [
    {
        number: '01',
        icon: PackageSearch,
        title: 'Find your materials.',
        text: 'Explore the market, compare suppliers, and find the right materials for the job.',
    },
    {
        number: '02',
        icon: Users,
        title: 'Buy alongside others.',
        text: 'Join a purchase cycle. Your order combines with other builders’ orders to unlock volume pricing.',
    },
    {
        number: '03',
        icon: Layers3,
        title: 'Build with more value.',
        text: 'Pay upfront, then receive any unlocked volume discount as a refund when the cycle is finalized.',
    },
];

export default function Landing() {
    const { auth } = usePage<SharedData>().props;
    return (
        <LandingLayout breadcrumbs={[]}>
            <Head title="Build better, together">
                <meta
                    name="description"
                    content="Source building materials, join shared purchase cycles, and put collective buying power behind your next project with BetterBloq."
                />
            </Head>
            <section className="shop-container grid items-center gap-12 pt-12 pb-14 lg:grid-cols-[1.1fr_1fr] lg:gap-20 lg:pt-20 lg:pb-20">
                <div className="reveal-in">
                    <p className="page-eyebrow mb-6 flex items-center gap-2.5">
                        <span className="bg-primary size-1.5 rounded-full" />
                        Built for the way you build
                    </p>
                    <h1 className="max-w-2xl text-[clamp(3.4rem,6.8vw,6.2rem)] leading-[0.98] font-medium tracking-[-0.065em]">
                        Better building
                        <br />
                        starts <span className="text-primary">together.</span>
                    </h1>
                    <p className="text-muted-foreground mt-7 max-w-md text-base leading-7 sm:text-lg sm:leading-8">
                        Your project might be individual. Your buying power doesn’t have to be. Find materials, pool demand, and make more of your
                        building budget.
                    </p>
                    <div className="mt-8 flex flex-wrap items-center gap-3">
                        <Button asChild size="lg" className="h-12 rounded-xl px-6">
                            <Link href={route('market')}>
                                Explore the market
                                <ArrowUpRight className="ml-2 size-4" />
                            </Link>
                        </Button>
                        <Button asChild variant="ghost" size="lg" className="h-12">
                            <a href="#how-it-works">
                                How it works
                                <ArrowDown className="ml-1 size-4" />
                            </a>
                        </Button>
                    </div>
                    <div className="text-muted-foreground mt-10 flex items-center gap-3 text-xs">
                        <span className="bg-secondary flex size-8 items-center justify-center rounded-full">
                            <Boxes className="size-4" />
                        </span>
                        From your first brick to your next big project.
                    </div>
                </div>
                <div className="reveal-in min-w-0" style={{ animationDelay: '100ms' }}>
                    <BuildingIllustration />
                </div>
            </section>
            <div className="bg-card/50 border-y">
                <div className="shop-container grid gap-5 py-6 text-sm sm:grid-cols-3 sm:gap-10 sm:py-7">
                    {[
                        { icon: ReceiptText, text: 'Clear upfront pricing' },
                        { icon: CalendarDays, text: 'Shared purchase cycles' },
                        { icon: Users, text: 'The power of buying together' },
                    ].map(({ icon: Icon, text }) => (
                        <div key={text} className="flex items-center gap-3">
                            <Icon className="text-primary size-4 shrink-0" />
                            <span className="font-medium">{text}</span>
                        </div>
                    ))}
                </div>
            </div>
            <section id="how-it-works" className="shop-container py-16 sm:py-24">
                <div className="mb-10 flex flex-col justify-between gap-5 sm:mb-14 sm:flex-row sm:items-end">
                    <div>
                        <p className="page-eyebrow mb-4">A simpler way to source</p>
                        <h2 className="page-title">Your project. Our collective strength.</h2>
                    </div>
                    <p className="text-muted-foreground max-w-xs text-sm leading-6">
                        Good materials and better buying power, with a clear path from browsing to building.
                    </p>
                </div>
                <div className="grid gap-5 md:grid-cols-3">
                    {steps.map(({ number, icon: Icon, title, text }) => (
                        <article key={number} className="surface p-7 sm:p-8">
                            <div className="mb-10 flex items-center justify-between">
                                <span className="bg-secondary text-secondary-foreground flex size-11 items-center justify-center rounded-xl">
                                    <Icon className="size-5" />
                                </span>
                                <span className="text-muted-foreground/60 text-xs tabular-nums">/ {number}</span>
                            </div>
                            <h3 className="text-xl font-semibold tracking-tight">{title}</h3>
                            <p className="text-muted-foreground mt-3 text-sm leading-7">{text}</p>
                        </article>
                    ))}
                </div>
            </section>
            <section className="shop-container">
                <div className="relative overflow-hidden rounded-[1.75rem] bg-[#2e4234] p-8 text-[#f5f5eb] sm:p-12 lg:flex lg:items-center lg:justify-between lg:gap-10">
                    <div className="relative z-10">
                        <p className="mb-4 text-xs font-medium tracking-[0.16em] text-[#becdb8] uppercase">Make your next project a better one</p>
                        <h2 className="max-w-xl text-3xl leading-tight font-medium tracking-[-0.04em] sm:text-4xl">
                            Let’s put something
                            <br />
                            great on solid ground.
                        </h2>
                    </div>
                    <div className="relative z-10 mt-7 lg:mt-0">
                        <Button asChild size="lg" className="h-12 rounded-xl bg-[#f0e6cf] px-6 text-[#2e4234] hover:bg-[#ffffff]">
                            <Link href={auth?.user ? route('dashboard') : route('register')}>
                                {auth?.user ? 'Open your workspace' : 'Start building with us'}
                                <ArrowRight className="ml-2 size-4" />
                            </Link>
                        </Button>
                        <p className="mt-3 text-xs text-[#becdb8]">A better way to buy. A better way to build.</p>
                    </div>
                    <div
                        className="pointer-events-none absolute -right-10 -bottom-24 size-72 rotate-45 rounded-3xl border border-white/10"
                        aria-hidden="true"
                    />
                </div>
            </section>
        </LandingLayout>
    );
}
