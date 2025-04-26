import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import LandingLayout from '@/layouts/landing-layout';
import { cn } from '@/lib/utils';
import { PlanType } from '@/types/model-types';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { Check, ChevronDownIcon, X } from 'lucide-react';
import { useEffect, useState } from 'react';

type PlanForm = {
    priceId: string;
    interval: string;
};

interface PropsType {
    flash: any;
    plans: { data: PlanType[] };
}

interface PlanLimit {
    id: number;
    value: number;
    model: string; 
    // text?: string; // Original commented property
}

interface PlanFeature {
    id: number;
    description: string;
    // included?: boolean; // Original commented property
}

interface PlanData {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    price: number;
    interval: 'monthly' | 'yearly';
    limits: PlanLimit[];
    features: PlanFeature[];
    is_popular: boolean;
    stripe_plan: string;
    // recommended?: boolean; // If directly on the plan object
    // billingNote?: string; // If directly on the plan object
}

interface PlansPageProps {
    monthlyPlans: { data: PlanData[] }; 
    yearlyPlans: { data: PlanData[] };
    flash: any;
}


export default function Plans(props: PlansPageProps) {
    const { flash, monthlyPlans, yearlyPlans } = props;

    const [isMonthly, setIsMonthly] = useState<boolean>(true);
    const { post, data, setData } = useForm<Required<PlanForm>>({
        priceId: '',
        interval: '',
    });
    const [isDangerZoneOpen, setIsDangerZoneOpen] = useState(false);

    const displayedPlans = isMonthly ? monthlyPlans.data : yearlyPlans.data;

    // Handler for the toggle switch
    const handleBillingToggle = (checked: boolean) => {
        setIsMonthly(!checked);
    };

    const handleCheckout = (plan: PlanData) => {
        setData('priceId', plan.stripe_plan);
        setData('interval', isMonthly ? 'monthly' : 'yearly');
    };

    useEffect(() => {
        if (data.priceId !== '') {
            post(route('checkout.create'));
        }
    }, [data.priceId]);

    const handleDeleteAccount = () => {
        if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
            router.post(route('account.delete'));
        }
    };

     useEffect(() => {
        if (flash?.message) {
            console.log("Flash:", flash.message);
        }
    }, [flash]);


    return (
        <LandingLayout breadcrumbs={[]}>
            <Head title="Plans & Pricing">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="bg-background/50 text-foreground min-h-screen pb-8">
                <div className="mx-auto max-w-screen-lg p-4">
                    {/* Flash Message Area */}
                    <div className="mb-4 min-h-[60px]">
                        {flash?.message && (
                            <Alert className="border border-green-400 bg-green-100 text-green-700">
                                <AlertTitle>Success!</AlertTitle>
                                <AlertDescription>{flash.message}</AlertDescription>
                            </Alert>
                        )}
                    </div>

                    {/* Page Header */}
                    <div className="mb-10 text-center">
                        <h1 className="mb-2 text-4xl font-bold">Choose Your Plan</h1>
                        <p className="text-lg text-gray-600">Unlock the full potential. Free for 14 days, then choose the perfect plan for you.</p>
                    </div>

                    {/* Billing Toggle */}
                    <div className="mb-8 flex items-center justify-center space-x-3">
                        <Label htmlFor="billingToggle" className={cn("cursor-pointer", !isMonthly && "text-muted-foreground")}>
                            Monthly
                        </Label>
                        <Switch
                            id="billingToggle"
                            checked={!isMonthly} // Switch is 'on' when isMonthly is false (Yearly selected)
                            onCheckedChange={handleBillingToggle}
                            aria-label={`Switch to ${isMonthly ? 'Annual' : 'Monthly'} billing`}
                         />
                        <Label htmlFor="billingToggle" className={cn("cursor-pointer", isMonthly && "text-muted-foreground")}>
                            Annual
                            {/* Conditionally show discount text only if yearly plans exist */}
                            {yearlyPlans.data.length > 0 && (
                                <span className="ml-1.5 rounded bg-green-100 px-1.5 py-0.5 text-xs font-medium text-green-700">
                                    Save 30% {/* Adjust discount % as needed */}
                                </span>
                            )}
                        </Label>
                    </div>

                    {/* Plan Cards */}
                    <form method="post" onSubmit={(e) => e.preventDefault()} className="mx-auto grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                        {displayedPlans.length > 0 ? (
                            displayedPlans.map((plan) => (
                                <Card
                                    key={plan.id}
                                    className={cn(
                                        'flex flex-col overflow-hidden bg-background transition-shadow duration-200 hover:shadow-lg',
                                        
                                        plan.is_popular && 'border-2 border-primary'
                                    )}
                                >
                            
                                    {!!plan.is_popular && (
                                        <div className="relative">
                                            <div className="absolute top-0 right-4 -mt-3 transform rounded-full bg-primary px-3 py-1 text-xs font-semibold text-primary-foreground shadow-md">
                                                Most Popular
                                            </div>
                                        </div>
                                    )}
                                    <CardHeader className="text-center pt-8">
                                        <CardTitle className="text-xl font-semibold">{plan.name}</CardTitle>
                                        {plan.description && <CardDescription className="mt-1 text-sm text-gray-500">{plan.description}</CardDescription>}
                                    </CardHeader>
                                    
                                    <CardContent className="flex flex-grow flex-col p-6">
                                        <div className="mb-6 text-center">
                                            <span className="text-4xl font-bold text-foreground">${plan.price}</span>
                                    
                                            <span className="text-sm text-muted-foreground">/{plan.interval === 'monthly' ? 'mo' : 'yr'}</span>
                                        </div>
                                    
                                        <ul className="mb-6 list-none space-y-3 text-sm">
                                    
                                            {plan.limits?.map((limit, i) => (
                                                <li key={`limit-${limit.id}-${i}`} className="flex items-start">
                                                    <Check className="mr-2 mt-0.5 h-5 w-5 flex-shrink-0 text-green-500" />
                                                    <span>Up to {limit.value} {limit.model}s</span>
                                                </li>
                                            ))}
                                            {plan.features?.map((feature, i) => (
                                                <li key={`feature-${feature.id}-${i}`} className="flex items-start">
                                                    <Check className="mr-2 mt-0.5 h-5 w-5 flex-shrink-0 text-green-500" />
                                                    <span>{feature.description}</span>
                                                    {/* If you had an 'included' flag:
                                                    {feature.included ? <Check className="mr-2 h-5 w-5 text-green-500" /> : <X className="mr-2 h-5 w-5 text-red-500" />}
                                                    */}
                                                </li>
                                            ))}
                                        </ul>
                                    </CardContent>
                                    
                                    <CardFooter className="p-6 pt-0">
                                        <Button
                                            // type="submit" // Use 'button' if not submitting the outer form
                                            type="button"
                                            onClick={() => handleCheckout(plan)}
                                            className="w-full"
                                            aria-label={`Select ${plan.name} plan`}
                                            // variant={plan.metadata?.highlight ? 'default' : 'outline'} // Example: Different button style
                                        >
                                            Select Plan
                                        </Button>
                                        {/* Use metadata for billing note */}
                                        {/* {plan.metadata?.billingNote && (
                                             <p className="mt-3 text-center text-xs text-muted-foreground">{plan.metadata.billingNote}</p>
                                        )} */}
                                    </CardFooter>
                                </Card>
                            ))
                        ) : (
                            // Message when no plans are available for the selected interval
                            <p className="col-span-full mt-8 text-center text-gray-500">
                                No {isMonthly ? 'monthly' : 'annual'} plans are currently available.
                            </p>
                        )}
                    </form>

                    {/* Danger Zone Dropdown (Keep if relevant here) */}
                    {/* Consider moving this to a user settings/profile page */}
                    <div className="mt-12 border-t pt-8 text-center">
                         <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                 <Button variant="outline" className="border-destructive text-destructive hover:bg-destructive/10">
                                    Danger Zone
                                     <ChevronDownIcon className={`ml-2 h-4 w-4 transition-transform`} />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent className="w-48">
                                <DropdownMenuItem onClick={handleDeleteAccount} className="text-destructive focus:bg-destructive/10 focus:text-destructive">
                                    Delete Account
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>

                </div>
            </div>
        </LandingLayout>
    );
}
