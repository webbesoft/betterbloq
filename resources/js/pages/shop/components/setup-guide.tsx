// src/pages/shop/components/SetupGuide.tsx (or your preferred path)
import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Progress } from '@/components/ui/progress';
import { X, ShoppingCart, ListOrdered, Info, Check, ShoppingBag } from 'lucide-react';
import { Link, router } from '@inertiajs/react';

interface Step {
    title: string;
    description: React.ReactNode;
    icon: React.ElementType;
}

const steps: Step[] = [
    {
        title: 'Explore the Market',
        description: (
            <>
                Discover products available for bulk purchase. Find materials for your next big build in the{' '}
                <Link href="/market" className="font-semibold text-primary underline hover:text-primary/80">
                    Marketplace
                </Link>.
            </>
        ),
        icon: ShoppingCart,
    },
    {
        title: 'Place Your First Order',
        description: (
            <>
                Once you find a product, proceed to create an order. If an active Purchase Pool is available, you will be able to join for potential discounts!
            </>
        ),
        icon: ShoppingBag,
    },
    {
        title: 'View Your Orders',
        description: (
            <>
                Track the status of your active orders and review completed ones in the{' '}
                <Link href="/orders" className="font-semibold text-primary underline hover:text-primary/80">
                    Orders
                </Link>{' '}
                section.
            </>
        ),
        icon: ListOrdered,
    },
];

interface SetupGuideProps {
    visibility: boolean;
    onUpdateVisibility: (visible: boolean) => void;
}

const LOCAL_STORAGE_DISMISSED_KEY = 'setupGuideDismissed';
const LOCAL_STORAGE_STEP_KEY = 'setupGuideLastStep';

export default function SetupGuide({ visibility, onUpdateVisibility }: SetupGuideProps) {
    const [currentStepIndex, setCurrentStepIndex] = useState<number>(0);
    const totalSteps = steps.length;

    useEffect(() => {
        const savedStep = localStorage.getItem(LOCAL_STORAGE_STEP_KEY);
        if (savedStep) {
            const stepIndex = parseInt(savedStep, 10);
            if (!isNaN(stepIndex) && stepIndex >= 0 && stepIndex < totalSteps) {
                setCurrentStepIndex(stepIndex);
            }
        }
    }, [totalSteps]);

    useEffect(() => {
        localStorage.setItem(LOCAL_STORAGE_STEP_KEY, currentStepIndex.toString());
    }, [currentStepIndex]);


    const handleDismiss = () => {
        localStorage.setItem(LOCAL_STORAGE_DISMISSED_KEY, 'true');
        onUpdateVisibility(false);
    };

    const handleNext = () => {
        if (currentStepIndex < totalSteps - 1) {
            setCurrentStepIndex(currentStepIndex + 1);
        }
    };

    const handlePrevious = () => {
        if (currentStepIndex > 0) {
            setCurrentStepIndex(currentStepIndex - 1);
        }
    };

    const handleFinish = () => {
        router.put(route('user.settings.completeGuide'), {}, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                localStorage.removeItem(LOCAL_STORAGE_STEP_KEY);
                localStorage.removeItem(LOCAL_STORAGE_DISMISSED_KEY);
                onUpdateVisibility(false);
                console.log('Setup guide marked as complete.');
            },
            onError: (errors) => {
                console.error('Failed to mark setup guide as complete:', errors);
            },
        });
    };

    if (!visibility) {
        return null;
    }

    const CurrentIcon = steps[currentStepIndex].icon;
    const progressValue = ((currentStepIndex + 1) / totalSteps) * 100;

    return (
        <Card className="mb-4 border-border/50 shadow-md">
            <CardHeader className="relative flex flex-row items-start gap-4 space-y-0 pb-4">
                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border bg-muted/50">
                    <CurrentIcon className="h-5 w-5 text-muted-foreground" />
                </div>
                <div className="flex-1">
                    <CardTitle className="text-lg">Getting Started Guide</CardTitle>
                    <CardDescription>
                        Follow these steps to get familiar with BetterBloq.
                    </CardDescription>
                </div>
                <Button
                    variant="ghost"
                    size="icon"
                    className="absolute top-3 right-3 h-7 w-7 text-muted-foreground hover:bg-muted/80"
                    onClick={handleDismiss}
                    aria-label="Dismiss guide"
                >
                    <X className="h-4 w-4" />
                </Button>
            </CardHeader>
            <CardContent className="space-y-4">
                <Progress value={progressValue} className="h-2" aria-label={`Step ${currentStepIndex + 1} of ${totalSteps}`} />
                <div>
                    <p className="font-semibold mb-1">{`Step ${currentStepIndex + 1}: ${steps[currentStepIndex].title}`}</p>
                    <div className="text-sm text-muted-foreground">
                        {steps[currentStepIndex].description}
                    </div>
                </div>

            </CardContent>
            <CardFooter className="flex justify-between">
                <Button
                    variant="ghost"
                    onClick={handlePrevious}
                    disabled={currentStepIndex === 0}
                >
                    Previous
                </Button>
                {currentStepIndex < totalSteps - 1 ? (
                    <Button onClick={handleNext}>
                        Next
                    </Button>
                ) : (
                    <Button onClick={handleFinish}>
                        <Check className="mr-2 h-4 w-4" /> Finish Guide
                    </Button>
                )}
            </CardFooter>
        </Card>
    );
}