import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button'; // Adjust path
import { Link } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react'; // Install: npm install lucide-react

export default function LandingPageBanner() {
    return (
        // Use bg-background which maps to your --custom-background via theme
        <div className="bg-background relative isolate px-6 pt-14 lg:px-8">
            {' '}
            <div className="absolute inset-x-0 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80" aria-hidden="true">
                <div
                    className="from-secondary to-primary relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr opacity-30 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]" // Use semantic colors if desired
                    style={{
                        // Consider simplifying or removing clip-path if not essential
                        clipPath:
                            'polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)',
                    }}
                ></div>
            </div>
            {/* Content Area */}
            <div className="mx-auto max-w-2xl py-32 sm:py-48 lg:py-56">
                {/* Announcement Badge - Using Shadcn Badge */}
                {/* <div className="hidden sm:mb-8 sm:flex sm:justify-center">
                    <Badge variant="outline" className="relative px-3 py-1 text-sm">
                        {' '}
                        Announcing our next round of funding.
                        <a href="#" className="text-primary hover:text-primary/80 font-semibold">
                            {' '}
                            <span className="absolute inset-0" aria-hidden="true"></span>
                            Read more <span aria-hidden="true">&rarr;</span>
                        </a>
                    </Badge>
                </div> */}

                {/* Text Content */}
                <div className="text-center">
                    <h1 className="text-foreground text-5xl font-semibold tracking-tight text-balance sm:text-7xl">
                        The Future of Housing Development
                    </h1>
                    <p className="text-foreground/80 mt-8 text-lg font-medium text-pretty sm:text-xl/8">
                        {' '}
                        Anim aute id magna aliqua ad ad non deserunt sunt. Qui irure qui lorem cupidatat commodo. Elit sunt amet fugiat veniam
                        occaecat.
                    </p>

                    <div className="mt-10 flex items-center justify-center gap-x-6">
                        <Button size="lg">
                            {' '}
                            <Link href={route('register')}>Get started</Link>
                        </Button>

                        {/* Button mapping: secondary-button -> secondary or outline */}
                        <Button variant="secondary" size="lg">
                            {' '}
                            {/* TODO: update route */}
                            <Link href={route('landing')} className="flex items-center">
                                Learn more <ArrowRight className="ml-2 h-4 w-4" />
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>
            {/* Second Background Gradient Blob */}
            <div
                className="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]"
                aria-hidden="true"
            >
                <div
                    // Corrected to use CSS variables defined in the theme
                    className="relative left-[calc(50%+3rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 bg-gradient-to-tr from-[var(--custom-primary)] to-[var(--custom-secondary)] opacity-30 sm:left-[calc(50%+36rem)] sm:w-[72.1875rem]"
                    // Alternative using theme colors directly (if defined in tailwind.config.js)
                    // className="relative left-[calc(50%+3rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 bg-gradient-to-tr from-primary to-secondary opacity-30 sm:left-[calc(50%+36rem)] sm:w-[72.1875rem]"
                    style={{
                        clipPath:
                            'polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)',
                    }}
                ></div>
            </div>
        </div>
    );
}
