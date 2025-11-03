import { Card, CardContent } from '@/components/ui/card';
import { CalendarCheck, FileText, NetworkIcon, TrendingUp } from 'lucide-react';

const howItWorksSteps = [
    {
        Icon: FileText,
        title: 'You tell us what you need',
        description: 'Through our simple online portal.',
        colorClass: 'text-primary',
    },
    {
        Icon: NetworkIcon,
        title: 'We pool your order with others',
        description: 'Creating meaningful collective buying power in our network.',
        colorClass: 'text-primary',
    },
    {
        Icon: TrendingUp,
        title: 'You get improved pricing',
        description: 'Making your budget go further and unlocking new possibilities.',
        colorClass: 'text-primary',
    },
    {
        Icon: CalendarCheck,
        title: 'Materials arrive when you need them',
        description: 'Coordinated with your project timeline for JIT delivery.',
        colorClass: 'text-primary',
    },
];

export default function HowItWorksSection() {
    return (
        <section id="how-it-works" className="bg-background w-full py-16 lg:py-24">
            {' '}
            {/* Added id for scroll */}
            <div className="container mx-auto px-6">
                <div className="mx-auto mb-12 max-w-3xl text-center lg:mb-16">
                    <h2 className="text-foreground text-3xl font-semibold tracking-tight capitalize lg:text-4xl">How It Works</h2>
                    <p className="text-muted-foreground mt-4 text-lg xl:mt-6">
                        Early access available now. It's simple, straightforward, and powerful. Unlock better material pricing in just a few steps.
                    </p>
                </div>

                <div className="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-4 xl:gap-10">
                    {' '}
                    {howItWorksSteps.map((step, index) => (
                        <Card key={index} className="flex flex-col items-center text-center">
                            <CardContent className="flex flex-grow flex-col items-center pt-8">
                                <div className={`mb-6 rounded-full p-4 bg-${step.colorClass.replace('text-', '')}-100 inline-block`}>
                                    {' '}
                                    <step.Icon className={`h-10 w-10 ${step.colorClass}`} strokeWidth={1.5} />
                                </div>
                                <h3 className="text-foreground mb-2 text-xl font-semibold">{step.title}</h3>
                                <p className="text-muted-foreground flex-grow text-sm">{step.description}</p>
                            </CardContent>
                        </Card>
                    ))}
                </div>
            </div>
        </section>
    );
}
