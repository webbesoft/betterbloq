import { Button } from '@/components/ui/button'; // Adjust path
import { Link } from '@inertiajs/react'; // Assuming you use Inertia for routing

export default function CtaSection() {
    return (
        // Use bg-accent which maps to your --custom-accent via the theme
        // text-accent-foreground ensures text is readable on this background
        <section className="bg-accent text-accent-foreground w-full">
            <div className="container mx-auto flex flex-col items-center px-4 py-12 text-center md:py-16 lg:py-20">
                <h2 className="mx-auto max-w-2xl text-2xl font-semibold tracking-tight xl:text-3xl">
                    Ready to streamline your coliving developments?
                    {/* Using font-bold for emphasis instead of a potentially low-contrast color */}
                    Take your business to the <span className="font-bold">next level.</span>
                </h2>

                <p className="text-accent-foreground/80 mt-6 max-w-3xl text-center">
                    {' '}
                    {/* Slightly reduced opacity */}
                    Stop juggling spreadsheets and disconnected tools. Unify your workflow, from lot analysis and procurement to project management
                    and invoicing, all on one platform designed for coliving developers.
                </p>

                <div className="mt-8 inline-flex w-full sm:w-auto">
                    {/*
            Button Variant Choice:
            - "default" uses your primary color. Check if orange (#d46f40) contrasts well enough with accent blue (#5b7a9f). It likely does.
            - "secondary" uses your secondary color. Check if light blue (#78cee3) contrasts well with accent blue. Maybe less ideal.
            - You could create a custom variant or use "outline" ensuring its colors work on accent bg.
            Let's use "default" (primary) for strong visibility.
           */}
                    <Button asChild size="lg" variant="default">
                        {/* Use Inertia Link or a standard <a> tag */}
                        <Link href={route('register')}>
                            {' '}
                            {/* Adjust route name as needed */}
                            Sign Up For Free
                        </Link>
                        {/* <a href="/register">Sign Up For Free</a> */}
                    </Button>
                </div>
            </div>
        </section>
    );
}
