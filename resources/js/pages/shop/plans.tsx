import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
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

export default function Plans(props: PropsType) {
    const { flash, plans } = props;

    const { data: plansData } = plans;

    const [isMonthly, setIsMonthly] = useState<boolean>(true);
    const { post, data, setData } = useForm<Required<PlanForm>>({
        priceId: '',
        interval: '',
    });
    const [isDangerZoneOpen, setIsDangerZoneOpen] = useState(false);

    const handleBillingToggle = (monthly: boolean) => {
        setIsMonthly(monthly);
    };

    const handleCheckout = (plan: PlanType) => {
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

    return (
        <LandingLayout>
            <Head title="Plans & Pricing">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="bg-background text-foreground min-h-screen py-16">
                <div className="mx-auto max-w-screen-lg p-4">
                    <div className="mb-8">
                        <Link href={route('landing')} className="text-primary hover:underline">
                            ← Back to Home
                        </Link>
                    </div>
                    <div className="mb-4">
                        {flash.message && (
                            <Alert className="border border-green-400 bg-green-100 text-green-700">
                                <AlertTitle>Success!</AlertTitle>
                                <AlertDescription>{flash.message}</AlertDescription>
                            </Alert>
                        )}
                    </div>
                    <div className="mb-10 text-center">
                        <h1 className="mb-2 text-4xl font-bold">Choose Your Plan</h1>
                        <p className="text-lg text-gray-600">Unlock the full potential. Free for 14 days, then choose the perfect plan for you.</p>
                    </div>
                    {/* Billing Toggle (Using Shadcn Switch) */}
                    <div className="mb-8 flex items-center justify-center space-x-4">
                        <Label htmlFor="billingToggle">Billing Cycle:</Label>
                        <Switch id="billingToggle" checked={isMonthly} onCheckedChange={handleBillingToggle} />
                        <span>
                            {isMonthly ? 'Monthly' : 'Annual'} <span className="ml-1 text-sm text-green-500">(Save 30%)</span>
                        </span>
                    </div>
                    `{/* Plan Cards using Shadcn Card */}
                    <form method="post" onSubmit={(e) => e.preventDefault()} className="mx-auto grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-2">
                        {plansData.map((plan, index) => (
                            <Card
                                key={index}
                                className={cn(
                                    'flex flex-col justify-between overflow-hidden',
                                    plan.recommended && 'border-4 border-[--custom-accent]',
                                )}
                            >
                                {plan.recommended && (
                                    <div className="absolute top-[-12px] left-1/2 -translate-x-1/2 transform rounded-full bg-yellow-400 px-3 py-1 text-xs font-bold text-white shadow-md">
                                        Most Popular
                                    </div>
                                )}
                                <CardHeader className="text-center">
                                    <CardTitle className="text-xl font-semibold">{plan.name}</CardTitle>
                                    <CardDescription className="text-sm text-gray-500">{plan.description}</CardDescription>
                                </CardHeader>
                                <CardContent className="p-6">
                                    <div className="mb-6 text-center">
                                        <span className="text-4xl font-bold text-[--custom-primary]">${plan.price}</span>
                                        <span className="text-sm text-gray-600"> / mo</span>
                                    </div>
                                    <ul className="mb-6 list-none space-y-3">
                                        {
                                            plan.limits &&
                                            plan.limits.map((limit, i) => (
                                                <li key={i} className="flex items-center text-sm text-accent-foreground">
                                                    <Check className="mr-2 h-5 w-5 text-green-500" />
                                                    Up to {limit.value} {limit.model}s
                                                    {/*{limit.text}*/}
                                                </li>
                                            ))
                                        }
                                        {plan.features &&
                                            plan.features.map((feature, i) => (
                                                <li key={i} className="flex items-center text-sm text-accent-foreground">
                                                    {/*{feature.included ? (*/}
                                                    <Check className="mr-2 h-5 w-5 text-green-500" />
                                                    {/*) : (*/}
                                                    {/*    <X className="mr-2 h-5 w-5 text-red-500" />*/}
                                                    {/*)}*/}
                                                    {feature.description}
                                                </li>
                                            ))}
                                    </ul>
                                </CardContent>
                                <div className="p-6">
                                    <Button
                                        type="submit"
                                        onClick={() => handleCheckout(plan)}
                                        className="bg-primary hover:bg-primary w-full cursor-grab"
                                    >
                                        Select
                                    </Button>
                                    {plan.billingNote && <p className="mt-2 text-center text-xs text-gray-500">{plan.billingNote}</p>}
                                </div>
                            </Card>
                        ))}
                    </form>
                    {/* Danger Zone Dropdown using Shadcn DropdownMenu */}
                    <div className="relative mt-12">
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button variant="outline" className="bg-red-100 font-semibold text-red-700 hover:bg-red-200">
                                    Danger Zone
                                    <ChevronDownIcon className={`ml-2 h-4 w-4 ${isDangerZoneOpen ? 'rotate-180' : ''}`} aria-hidden="true" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent className="w-48">
                                <DropdownMenuItem onClick={handleDeleteAccount} className="text-red-700 focus:bg-red-100">
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
