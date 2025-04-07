import { KanbanSquare, Library, MapPin, Receipt, ShoppingCart, Users } from 'lucide-react'; // Ensure lucide-react is installed

import { Card, CardContent } from '@/components/ui/card'; // Adjust path if needed

// Feature data from above
const features = [
    {
        Icon: ShoppingCart,
        title: 'Smart Procurement & Pooling',
        description:
            'Optimize material costs with Just-In-Time bulk purchasing and intelligent order pooling across multiple coliving projects. Reduce waste and manage supplier relations effectively.',
        colorClass: 'text-primary', // Use primary color from theme
    },
    {
        Icon: KanbanSquare,
        title: 'Integrated Project Management',
        description:
            'Oversee timelines, tasks, budgets, and team collaboration from groundbreaking to tenant-ready units. Track progress across all your coliving developments in one unified dashboard.',
        colorClass: 'text-secondary', // Use secondary color
    },
    {
        Icon: Receipt,
        title: 'Streamlined Invoicing & Finance',
        description:
            'Generate, send, and track contractor and supplier invoices effortlessly. Monitor project financials and cash flow with clear visibility and simplified payment management.',
        colorClass: 'text-accent', // Use accent color
    },
    {
        Icon: MapPin,
        title: 'Data-Driven Lot Analysis',
        description:
            'Evaluate potential sites faster with integrated zoning data checks, automated feasibility reports, and coliving-specific yield projections. Make informed acquisition decisions confidently.',
        colorClass: 'text-primary', // Reuse colors for visual rhythm
    },
    {
        Icon: Users,
        title: 'Verified Contractor Marketplace',
        description:
            'Access a curated network of vetted contractors with proven experience in coliving builds. Streamline bidding, contract management, and communication within the platform.',
        colorClass: 'text-secondary',
    },
    {
        Icon: Library,
        title: 'Centralized Plan Management',
        description:
            'Store, version, and collaborate on architectural plans, permits, and essential documents. Ensure easy access and up-to-date information for all project stakeholders.',
        colorClass: 'text-accent',
    },
];

export default function FeaturesSection() {
    return (
        // Use bg-background, py for padding
        <section className="bg-background w-full py-16 lg:py-24">
            <div className="container mx-auto px-6">
                {/* Section Header */}
                <div className="mx-auto mb-12 max-w-3xl text-center lg:mb-16">
                    <h2 className="text-foreground text-3xl font-semibold tracking-tight capitalize lg:text-4xl">
                        Everything You Need to Scale <span className="text-primary">Coliving Development</span>
                        {/* Alternative with underline: */}
                        {/* <span className="underline decoration-primary decoration-2 underline-offset-4">Coliving Development</span> */}
                    </h2>
                    <p className="text-muted-foreground mt-4 text-lg xl:mt-6">
                        Our unified platform streamlines every phase, from site analysis and procurement to project completion and invoicing,
                        specifically tailored for coliving developers.
                    </p>
                </div>

                <div className="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3 xl:gap-12">
                    {features.map((feature, index) => (
                        <Card key={index} className="flex flex-col">
                            {' '}
                            {/* Use Card component */}
                            <CardContent className="flex flex-grow flex-col pt-6">
                                {' '}
                                {/* Ensure content grows */}
                                <feature.Icon
                                    className={`mb-4 h-10 w-10 ${feature.colorClass}`}
                                    strokeWidth={1.5} // Adjust stroke width for visual preference
                                />
                                <h3 className="text-foreground mb-2 text-xl font-semibold">{feature.title}</h3>
                                <p className="text-muted-foreground flex-grow text-sm">
                                    {' '}
                                    {/* Use text-sm and allow paragraph to grow */}
                                    {feature.description}
                                </p>
                                {/* Removed the "Read More" link as it's usually not needed here */}
                            </CardContent>
                        </Card>
                    ))}
                </div>
            </div>
        </section>
    );
}
