import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import React, { useState } from 'react';

export default function DeveloperPreviewSection() {
    const [email, setEmail] = useState('');

    const handleSubscribe = (e: React.FormEvent) => {
        e.preventDefault();
        console.log('Subscribing email:', email);
        alert('Thank you for subscribing!');
        setEmail('');
    };

    return (
        <section className="bg-accent text-accent-foreground w-full">
            <div className="container mx-auto flex flex-col items-center px-4 py-12 text-center md:py-16 lg:py-20">
                <h2 className="mx-auto max-w-2xl text-2xl font-semibold tracking-tight xl:text-3xl">
                    FOR DEVELOPERS: <span className="text-primary font-bold">WHAT'S COMING</span>
                </h2>

                <p className="text-accent-foreground/90 mt-6 max-w-3xl text-center">
                    Our purchasing pool is just the first step. We're building a platform that will transform how housing gets developed - from
                    finding land to coordinating construction to securing financing.
                </p>
                <p className="text-accent-foreground/90 mt-4 max-w-3xl text-center font-medium">
                    Join our developer community to be first in line as we launch each new feature.
                </p>

                <form onSubmit={handleSubscribe} className="mt-8 flex w-full max-w-md flex-col items-center justify-center gap-4 sm:flex-row">
                    <Input
                        type="email"
                        placeholder="Enter your email"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        required
                        className="text-foreground flex-grow" // Ensure input text is visible
                    />
                    <Button type="submit" size="lg" variant="default" className="sm:w-auto">
                        Subscribe
                    </Button>
                </form>
            </div>
        </section>
    );
}
