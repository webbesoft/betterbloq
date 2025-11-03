import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/react';
import { Zap } from 'lucide-react';

export default function JoinCommunitySection() {
    return (
        <section className="bg-background w-full py-16 lg:py-24">
            <div className="container mx-auto px-6">
                <div className="mx-auto max-w-3xl text-center lg:mb-12">
                    <Zap className="text-primary mx-auto mb-4 h-12 w-12" strokeWidth={1.5} />
                    <h2 className="text-foreground text-3xl font-semibold tracking-tight capitalize lg:text-4xl">Join Our Community</h2>
                    <p className="text-muted-foreground mt-4 text-lg xl:mt-6">
                        Every person who joins strengthens our network, creating a virtuous cycle of better pricing for everyone. Be part of the
                        solution to affordable housing - one material order at a time.
                    </p>
                </div>

                <div className="mt-8 flex justify-center">
                    <Button asChild size="lg" className="px-10 py-6 text-lg">
                        <Link href={route('register')}>JOIN NOW & Get Early Access</Link>
                    </Button>
                </div>
                <p className="text-muted-foreground mt-4 text-center text-sm">Early access available now. Simple, straightforward, and powerful.</p>
            </div>
        </section>
    );
}
