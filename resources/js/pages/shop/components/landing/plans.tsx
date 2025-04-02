import { Button } from '@/components/ui/button'; // Adjust path
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'; // Adjust path
import { cn } from '@/lib/utils'; // Import cn utility if you haven't already (from shadcn setup)
import { Check, X } from 'lucide-react'; // Ensure lucide-react is installed

const FeatureListItem = ({ text, included }: { text: string; included: boolean }) => (
    <li className="flex items-start">
        {' '}
        {/* Use items-start for long text alignment */}
        {included ? (
            <Check className="text-primary mt-0.5 mr-2 h-5 w-5 flex-shrink-0" /> // Adjusted spacing/alignment
        ) : (
            <X className="text-muted-foreground mt-0.5 mr-2 h-5 w-5 flex-shrink-0" />
        )}
        <span className={`text-sm ${included ? 'text-foreground/90' : 'text-muted-foreground line-through'}`}>
            {' '}
            {/* Slightly less emphasis than title */}
            {text}
        </span>
    </li>
);

const plans = [
    {
        name: 'Starter',
        description: 'Ideal for small teams or single coliving projects.',
        priceMonthly: 49,
        priceSuffix: '/ project / month',
        billingNote: 'Billed monthly',
        ctaText: 'Get Started',
        features: [
            { text: 'Basic Project Management (1 Project)', included: true },
            { text: 'Basic Plan Management (Up to 10 Plans)', included: true },
            { text: 'Standard Invoicing', included: true },
            { text: 'Basic Lot Analysis Tools', included: true },
            { text: 'Standard Support', included: true },
            { text: 'Contractor Marketplace Access', included: false },
            { text: 'Procurement & Order Pooling', included: false },
            { text: 'Advanced Reporting', included: false },
        ],
        recommended: false,
    },
    {
        name: 'Pro',
        description: 'Best for growing developers managing multiple projects.',
        priceMonthly: 199,
        priceSuffix: '/ month',
        billingNote: 'Billed annually or $249 monthly',
        ctaText: 'Choose Pro',
        features: [
            { text: 'Advanced Project Management (Up to 10 Projects)', included: true },
            { text: 'Unlimited Plan Management', included: true },
            { text: 'Advanced Invoicing & Payments', included: true },
            { text: 'Advanced Lot Analysis & Feasibility', included: true },
            { text: 'Priority Support', included: true },
            { text: 'Contractor Marketplace & Bidding', included: true },
            { text: 'Basic Procurement & Order Pooling', included: true },
            { text: 'Standard Reporting Dashboard', included: true },
        ],
        recommended: true, // Highlight this plan
    },
    {
        name: 'Enterprise',
        description: 'Tailored solutions for large-scale coliving operations.',
        priceMonthly: 'Custom',
        priceSuffix: '',
        billingNote: 'Contact us for a quote',
        ctaText: 'Contact Sales',
        features: [
            { text: 'Unlimited Projects & Users', included: true },
            { text: 'Unlimited Plan Management', included: true },
            { text: 'Customizable Invoicing & Integrations', included: true },
            { text: 'Comprehensive Lot Analysis Suite', included: true },
            { text: 'Dedicated Account Manager & Support', included: true },
            { text: 'Full Contractor Marketplace Features', included: true },
            { text: 'Advanced Procurement & Pooling Logistics', included: true },
            { text: 'Custom Reporting & API Access', included: true },
        ],
        recommended: false,
    },
];

export default function PricingSection() {
    return (
        <section className="bg-background w-full py-16 lg:py-24">
            <div className="container mx-auto px-6">
                {/* Section Header */}
                <div className="mx-auto mb-12 max-w-3xl text-center lg:mb-16">
                    <h2 className="text-foreground text-3xl font-semibold tracking-tight capitalize lg:text-4xl">Simple, Transparent Pricing</h2>
                    {/* Optional: Decorative underline using theme color */}
                    <div className="mt-3 mb-4 flex justify-center">
                        <div className="bg-primary h-1 w-20 rounded-full"></div>
                    </div>
                    <p className="text-muted-foreground mt-4 text-lg xl:mt-6">
                        Choose the plan that scales with your coliving development needs. No hidden fees, just powerful tools.
                    </p>
                </div>

                {/* Pricing Grid */}
                {/* Adjust grid columns for potentially 3 plans */}
                <div className="mx-auto grid max-w-6xl grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3 xl:gap-10">
                    {plans.map((plan) => (
                        <Card
                            key={plan.name}
                            // Add conditional styling for the recommended plan
                            className={cn(
                                'flex flex-col', // Ensure cards are flex columns
                                plan.recommended && 'border-primary ring-primary/20 border-2 ring-2', // Highlight recommended
                            )}
                        >
                            <CardHeader className="pb-4">
                                <CardTitle className="mb-1 text-2xl">{plan.name}</CardTitle>
                                <CardDescription>{plan.description}</CardDescription>
                            </CardHeader>
                            <CardContent className="flex-grow pt-0 pb-6">
                                {' '}
                                {/* Adjust padding */}
                                {/* Price */}
                                <div className="mb-6">
                                    <p className="text-foreground text-4xl font-bold">
                                        {typeof plan.priceMonthly === 'number' ? `$${plan.priceMonthly}` : plan.priceMonthly}
                                        <span className="text-muted-foreground ml-1 text-sm font-normal">{plan.priceSuffix}</span>
                                    </p>
                                    <p className="text-muted-foreground mt-1 text-xs">{plan.billingNote}</p>
                                </div>
                                {/* Features List */}
                                <h4 className="text-foreground mb-3 text-sm font-medium">What's included:</h4>
                                <ul className="space-y-3">
                                    {plan.features.map((feature) => (
                                        <FeatureListItem key={feature.text} text={feature.text} included={feature.included} />
                                    ))}
                                </ul>
                            </CardContent>
                            <CardFooter>
                                <Button
                                    // Use secondary variant for non-recommended, default (primary) for recommended
                                    variant={plan.recommended ? 'default' : 'outline'}
                                    className="w-full"
                                    size="lg"
                                >
                                    {plan.ctaText}
                                </Button>
                            </CardFooter>
                        </Card>
                    ))}
                </div>
            </div>
        </section>
    );
}
