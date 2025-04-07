import React, { useState } from 'react';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { ChevronDown, X } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Progress } from '@/components/ui/progress';

interface Step {
    id: number;
    title: string;
    description: string;
}

interface SetupGuideProps {
    visibility?: boolean;
    onUpdateVisibility?: (value: (((prevState: boolean) => boolean) | boolean)) => void;
}

const SetupGuide = ({ visibility, onUpdateVisibility }: SetupGuideProps) => {
    const steps: Step[] = [
        { id: 1, title: 'Choose Plan', description: 'Select the best plan for your needs.' },
        { id: 2, title: 'Create Project', description: 'Set up your new project.' },
        { id: 3, title: 'Setup Project Milestones', description: 'Define the key milestones for your project.' },
        { id: 4, title: 'Make an Order', description: 'Finalize your order.' }
    ];

    const [openStates, setOpenStates] = useState<{ [key: number]: boolean }>(
        steps.reduce((acc, step) => {
            acc[step.id] = step.id === 1;
            return acc;
        }, {})
    );

    const handleOpenChange = (id: number, newOpen: boolean) => {
        setOpenStates((prev) => ({ ...prev, [id]: newOpen }));
    };

    const progress = (Object.values(openStates).filter(open => open).length / steps.length) * 100;

    return (
        <Card className="container mx-auto py-10 p-4">
            <div className={'flex items-center justify-between mb-4'}>
                <h2 className="text-2xl font-bold">Setup Guide</h2>
                <Button onClick={() => onUpdateVisibility!(!visibility)} variant={'ghost'}>
                    <X className={'text-destructive'} />
                </Button>
            </div>

            <Progress value={progress} className="mb-4" />

            <div className="space-y-4">
                {steps.map((step) => (
                    <div key={step.id} className={'border border-gray-300 rounded-md p-4 bg-accent'}>
                        <CardHeader>
                            <CardTitle className="flex items-center justify-between">
                                <div className="flex items-center">
                                    <Badge className="mr-2">Step {step.id}</Badge>
                                    {step.title}
                                </div>
                                <Collapsible open={openStates[step.id]} onOpenChange={(newOpen) => handleOpenChange(step.id, newOpen)}>
                                    <CollapsibleTrigger asChild>
                                        <ChevronDown
                                            className={`h-4 w-4 transition-transform duration-200 ${
                                                openStates[step.id] ? 'rotate-180' : ''
                                            }`}
                                        />
                                    </CollapsibleTrigger>
                                </Collapsible>
                            </CardTitle>
                            <CardDescription>{step.description}</CardDescription>
                        </CardHeader>

                        <Collapsible open={openStates[step.id]} onOpenChange={(newOpen) => handleOpenChange(step.id, newOpen)}>
                            {/* The CollapsibleTrigger here was causing issues, it's removed as the ChevronDown in CardTitle acts as the trigger */}
                            <CollapsibleContent className="py-4">
                                {/* Content for the step */}
                                <p>Content for {step.description}</p>
                                {/* Add specific UI elements for this step */}
                            </CollapsibleContent>
                        </Collapsible>
                    </div>
                ))}
            </div>
        </Card>
    );
};

export default SetupGuide;
