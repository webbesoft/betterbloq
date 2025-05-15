import { Building, TrendingUpIcon, Users2 } from 'lucide-react'; // Example icons

export default function WhyItMattersSection() {
    return (
        <section className="bg-secondary/20 w-full py-16 lg:py-24">
            {' '}
            {/* Using a slightly different background for variation */}
            <div className="container mx-auto px-6">
                <div className="mx-auto mb-12 max-w-3xl text-center lg:mb-16">
                    <h2 className="text-foreground text-3xl font-semibold tracking-tight lg:text-4xl">Why This Matters</h2>
                    <div className="mt-3 mb-4 flex justify-center">
                        <div className="bg-primary h-1 w-20 rounded-full"></div>
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-8 text-center md:grid-cols-3">
                    <div className="space-y-3">
                        <Building className="text-primary mx-auto h-12 w-12" strokeWidth={1.5} />
                        <h3 className="text-foreground text-xl font-semibold">More Projects Possible</h3>
                        <p className="text-muted-foreground">
                            When materials cost less, more projects become financially viable, from dream homes to essential renovations.
                        </p>
                    </div>
                    <div className="space-y-3">
                        <Users2 className="text-primary mx-auto h-12 w-12" strokeWidth={1.5} />
                        <h3 className="text-foreground text-xl font-semibold">Improved Neighborhoods</h3>
                        <p className="text-muted-foreground">
                            When homeowners save on renovations and developers can build affordably, entire neighborhoods improve and thrive.
                        </p>
                    </div>
                    <div className="space-y-3">
                        <TrendingUpIcon className="text-primary mx-auto h-12 w-12" strokeWidth={1.5} />
                        <h3 className="text-foreground text-xl font-semibold">Fairer Competition</h3>
                        <p className="text-muted-foreground">
                            When small developers can compete with larger ones on material costs, communities win with diverse and innovative housing.
                        </p>
                    </div>
                </div>

                <p className="text-foreground/90 mx-auto mt-12 max-w-4xl text-center text-xl leading-relaxed">
                    This isn't just about saving money. It's about what becomes possible when we remove artificial barriers to building better,
                    together.
                </p>
            </div>
        </section>
    );
}
