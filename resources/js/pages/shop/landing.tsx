import { LandingNavigation } from '@/components/landing-navigation';
import LandingLayout from '@/layouts/landing-layout';
import { NavItem } from '@/types';
import { Head } from '@inertiajs/react';

const navItems: NavItem[] = [
    {
        title: 'Home',
        url: '#',
    },
];

export default function Landing() {
    return (
        <>
            <Head title="BulkBuy">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <LandingLayout>
                <div className="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">
                    <LandingNavigation items={navItems} />
                    <div className="relative px-6 pt-14 lg:px-8">
                        <div className="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80" aria-hidden="true">
                            <div
                                className="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-[#0A363C] to-[#f88687] opacity-30 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]"
                                style={{
                                    clipPath:
                                        'polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)',
                                }}
                            ></div>
                        </div>
                        <div className="mx-auto max-w-2xl py-32 sm:py-48 lg:py-56">
                            <div className="hidden sm:mb-8 sm:flex sm:justify-center">
                                <div className="relative rounded-full px-3 py-1 text-sm/6 text-gray-600 ring-1 ring-gray-900/10 hover:ring-gray-900/20">
                                    Announcing our next round of funding.{' '}
                                    <a href="#" className="text-primaryLighter font-semibold">
                                        <span className="absolute inset-0" aria-hidden="true"></span>Read more <span aria-hidden="true">&rarr;</span>
                                    </a>
                                </div>
                            </div>
                            <div className="text-center">
                                <h1 className="text-5xl font-semibold tracking-tight text-balance text-gray-900 sm:text-7xl">
                                    The Future of Housing Development
                                </h1>
                                <p className="mt-8 text-lg font-medium text-pretty text-gray-500 sm:text-xl/8">
                                    Anim aute id magna aliqua ad ad non deserunt sunt. Qui irure qui lorem cupidatat commodo. Elit sunt amet fugiat
                                    veniam occaecat.
                                </p>
                                <div className="mt-10 flex items-center justify-center gap-x-6">
                                    <a
                                        href="#"
                                        className="bg-primary hover:bg-primaryLighter rounded-md px-5 py-3.5 text-sm font-semibold text-white opacity-85 shadow-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                                    >
                                        Get started
                                    </a>
                                    <a href="#" className="text-sm/6 font-semibold text-gray-900">
                                        Learn more <span aria-hidden="true">→</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div
                            className="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]"
                            aria-hidden="true"
                        >
                            <div
                                className="relative left-[calc(50%+3rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 bg-gradient-to-tr from-[#0A363C] to-[#f88687] opacity-30 sm:left-[calc(50%+36rem)] sm:w-[72.1875rem]"
                                style={{
                                    clipPath:
                                        'polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)',
                                }}
                            ></div>
                        </div>
                    </div>

                    {/* <!--    platform features--> */}
                    <div className="bg-primary h-screen"></div>

                    {/* <!--    Upselling--> */}
                    <div className="flex min-h-[66vh] items-center justify-center">
                        <p>what we can do for you</p>
                    </div>

                    {/* <!--    Plans--> */}
                    <div className="bg-darkened h-screen">
                        <p>Plans</p>
                    </div>

                    {/* <!--    Work with us--> */}
                    <div className="flex min-h-[50vh] items-center justify-center">
                        <p>how you can work with us</p>
                    </div>
                </div>
            </LandingLayout>
        </>
    );
}
