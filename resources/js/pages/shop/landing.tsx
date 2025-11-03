import LandingLayout from '@/layouts/landing-layout';
import { Head } from '@inertiajs/react';

import LandingPageBanner from './components/landing/banner';
import DeveloperPreviewSection from './components/landing/developer-preview';
import HowItWorksSection from './components/landing/how-it-works';
import JoinCommunitySection from './components/landing/join-community';
import ProblemSolutionSection from './components/landing/problem-solution';
import WhyItMattersSection from './components/landing/why-it-matters';

export default function Landing() {
    return (
        <>
            <Head title="Home | Let's Build Better Together">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <LandingLayout breadcrumbs={[]}>
                {' '}
                <div className="landing-page flex flex-col items-center gap-0 bg-[var(--background)] text-[var(--custom-text)]">
                    {' '}
                    <LandingPageBanner />
                    <ProblemSolutionSection />
                    <HowItWorksSection />
                    <WhyItMattersSection />
                    <JoinCommunitySection />
                    <DeveloperPreviewSection />
                </div>
            </LandingLayout>
        </>
    );
}
