import { LandingFooter } from '@/components/landing-footer';
import { LandingNavigation } from '@/components/landing-navigation';
import LandingLayout from '@/layouts/landing-layout';
import { NavItem } from '@/types';
import { Head } from '@inertiajs/react';
import LandingPageBanner from './components/landing/banner';
import CtaSection from './components/landing/call-to-action';
import FeaturesSection from './components/landing/features';
import PricingSection from './components/landing/plans';

export default function Landing() {
    return (
        <>
            <Head title="Home">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <LandingLayout breadcrumbs={[]}>
                <div className="landing-page flex min-h-screen flex-col items-center gap-4 bg-[var(--background)] text-[var(--custom-text)] lg:justify-center">
                    {/* Banner */}
                    <LandingPageBanner />

                    {/* Features */}
                    <FeaturesSection />

                    {/* <!--    Upselling--> */}
                    {/*<div className="flex min-h-[66vh] w-full items-center justify-center bg-[var(--secondary-200)]">*/}
                    {/*    <p>what we can do for you</p>*/}
                    {/*</div>*/}

                    <PricingSection />

                    <CtaSection />
                </div>
            </LandingLayout>
        </>
    );
}
