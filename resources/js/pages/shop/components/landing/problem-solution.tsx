export default function ProblemSolutionSection() {
    return (
        <section className="bg-background w-full py-16 lg:py-24">
            <div className="container mx-auto px-6">
                <div className="grid grid-cols-1 items-start gap-12 md:grid-cols-2 md:gap-16">
                    {/* The Problem */}
                    <div className="space-y-4">
                        <h2 className="text-primary text-2xl font-semibold tracking-tight lg:text-3xl">The Problem Is Simple</h2>
                        <p className="text-foreground/80 text-lg leading-relaxed">
                            If you've ever tackled a building project, you know the truth: Materials eat up 40-60% of your budget. And unless you're
                            buying truckloads, you're paying retail prices that are 30-40% above wholesale.
                        </p>
                        <p className="text-foreground/80 text-lg leading-relaxed">
                            This reality shapes everything - from the quality of materials you can afford to the financial viability of your entire
                            project.
                        </p>
                    </div>

                    {/* A Better Way Forward */}
                    <div className="space-y-4">
                        <h2 className="text-primary text-2xl font-semibold tracking-tight lg:text-3xl">A Better Way Forward</h2>
                        <p className="text-foreground/80 text-lg leading-relaxed">
                            We started with a simple question: What if everyone could buy materials at better prices? Not just big developers. Not
                            just national builders. <strong>Everyone:</strong>
                        </p>
                        <ul className="text-foreground/80 list-inside list-disc space-y-2 pl-4 text-lg">
                            <li>The contractor building a dream home</li>
                            <li>The developer creating affordable housing</li>
                            <li>The flipper transforming neglected properties</li>
                        </ul>
                        <p className="text-foreground/80 mt-4 text-lg leading-relaxed">
                            So we created something new: a purchasing pool that combines orders across our network, unlocking better pricing for all.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    );
}
