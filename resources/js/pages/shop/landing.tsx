import { LandingFooter } from '@/components/landing-footer';
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
            <Head title="Home">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <LandingLayout>
                <div className="flex min-h-screen flex-col items-center gap-4 bg-[var(--background)] text-[var(--custom-text)] lg:justify-center">
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
                                <div className="relative rounded-full px-3 py-1 text-sm/6 text-[var(--custom-text)] ring-1 ring-gray-900/10 hover:ring-gray-900/20">
                                    Announcing our next round of funding.{' '}
                                    <a href="#" className="font-semibold">
                                        <span className="absolute inset-0" aria-hidden="true"></span>Read more <span aria-hidden="true">&rarr;</span>
                                    </a>
                                </div>
                            </div>
                            <div className="text-center">
                                <h1 className="text-5xl font-semibold tracking-tight text-balance text-[var(--custom-accent)] sm:text-7xl">
                                    The Future of Housing Development
                                </h1>
                                <p className="mt-8 text-lg font-medium text-pretty sm:text-xl/8">
                                    Anim aute id magna aliqua ad ad non deserunt sunt. Qui irure qui lorem cupidatat commodo. Elit sunt amet fugiat
                                    veniam occaecat.
                                </p>
                                <div className="mt-10 flex items-center justify-center gap-x-6">
                                    <a href="#" className="button accent-button">
                                        Get started
                                    </a>
                                    <a href="#" className="button secondary-button">
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
                                className="relative left-[calc(50%+3rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 bg-gradient-to-tr from-[var(--primary)] to-[var(--secondary)] opacity-30 sm:left-[calc(50%+36rem)] sm:w-[72.1875rem]"
                                style={{
                                    clipPath:
                                        'polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)',
                                }}
                            ></div>
                        </div>
                    </div>

                    {/* <!--    platform features--> */}
                        <section className="w-full bg-[var(--background)]">
                            <div className="container mx-auto px-6 py-10">
                                <h1 className="text-2xl font-semibold text-[var(--custom-text)] capitalize lg:text-3xl dark:text-white">
                                    explore our <br /> awesome <span className="underline decoration-blue-500">Components</span>
                                </h1>

                                <p className="mt-4 text-gray-500 xl:mt-6 dark:text-gray-300">
                                    Lorem ipsum dolor sit amet consectetur, adipisicing elit. Nostrum quam voluptatibus
                                </p>

                                <div className="mt-8 grid grid-cols-1 gap-8 md:grid-cols-2 xl:mt-12 xl:grid-cols-3 xl:gap-16">
                                    <div className="space-y-3">
                                        <span className="inline-block rounded-full bg-blue-100 p-3 text-blue-500 dark:bg-blue-500 dark:text-white">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                className="h-6 w-6"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
                                                />
                                            </svg>
                                        </span>

                                        <h1 className="text-xl font-semibold text-gray-700 capitalize dark:text-white">Copy & paste components</h1>

                                        <p className="text-gray-500 dark:text-gray-300">
                                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Provident ab nulla quod dignissimos vel non
                                            corrupti doloribus voluptatum eveniet
                                        </p>

                                        <a
                                            href="#"
                                            className="-mx-1 inline-flex transform items-center text-sm text-blue-500 capitalize transition-colors duration-300 hover:text-blue-600 hover:underline dark:text-blue-400 dark:hover:text-blue-500"
                                        >
                                            <span className="mx-1">read more</span>
                                            <svg
                                                className="mx-1 h-4 w-4 rtl:-scale-x-100"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z"
                                                    clip-rule="evenodd"
                                                ></path>
                                            </svg>
                                        </a>
                                    </div>

                                    <div className="space-y-3">
                                        <span className="inline-block rounded-full bg-blue-100 p-3 text-blue-500 dark:bg-blue-500 dark:text-white">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                className="h-6 w-6"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"
                                                />
                                            </svg>
                                        </span>

                                        <h1 className="text-xl font-semibold text-gray-700 capitalize dark:text-white">Zero Configuration</h1>

                                        <p className="text-gray-500 dark:text-gray-300">
                                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Provident ab nulla quod dignissimos vel non
                                            corrupti doloribus voluptatum eveniet
                                        </p>

                                        <a
                                            href="#"
                                            className="-mx-1 inline-flex transform items-center text-sm text-blue-500 capitalize transition-colors duration-300 hover:text-blue-600 hover:underline dark:text-blue-400 dark:hover:text-blue-500"
                                        >
                                            <span className="mx-1">read more</span>
                                            <svg
                                                className="mx-1 h-4 w-4 rtl:-scale-x-100"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z"
                                                    clip-rule="evenodd"
                                                ></path>
                                            </svg>
                                        </a>
                                    </div>

                                    <div className="space-y-3">
                                        <span className="inline-block rounded-full bg-blue-100 p-3 text-blue-500 dark:bg-blue-500 dark:text-white">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                className="h-6 w-6"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                                />
                                            </svg>
                                        </span>

                                        <h1 className="text-xl font-semibold text-gray-700 capitalize dark:text-white">New Components Every month</h1>

                                        <p className="text-gray-500 dark:text-gray-300">
                                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Provident ab nulla quod dignissimos vel non
                                            corrupti doloribus voluptatum eveniet
                                        </p>

                                        <a
                                            href="#"
                                            className="-mx-1 inline-flex transform items-center text-sm text-blue-500 capitalize transition-colors duration-300 hover:text-blue-600 hover:underline dark:text-blue-400 dark:hover:text-blue-500"
                                        >
                                            <span className="mx-1">read more</span>
                                            <svg
                                                className="mx-1 h-4 w-4 rtl:-scale-x-100"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z"
                                                    clip-rule="evenodd"
                                                ></path>
                                            </svg>
                                        </a>
                                    </div>

                                    <div className="space-y-3">
                                        <span className="inline-block rounded-full bg-blue-100 p-3 text-blue-500 dark:bg-blue-500 dark:text-white">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                className="h-6 w-6"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"
                                                />
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"
                                                />
                                            </svg>
                                        </span>

                                        <h1 className="text-xl font-semibold text-gray-700 capitalize dark:text-white">elegant Dark Mode</h1>

                                        <p className="text-gray-500 dark:text-gray-300">
                                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Provident ab nulla quod dignissimos vel non
                                            corrupti doloribus voluptatum eveniet
                                        </p>

                                        <a
                                            href="#"
                                            className="-mx-1 inline-flex transform items-center text-sm text-blue-500 capitalize transition-colors duration-300 hover:text-blue-600 hover:underline dark:text-blue-400 dark:hover:text-blue-500"
                                        >
                                            <span className="mx-1">read more</span>
                                            <svg
                                                className="mx-1 h-4 w-4 rtl:-scale-x-100"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z"
                                                    clip-rule="evenodd"
                                                ></path>
                                            </svg>
                                        </a>
                                    </div>

                                    <div className="space-y-3">
                                        <span className="inline-block rounded-full bg-blue-100 p-3 text-blue-500 dark:bg-blue-500 dark:text-white">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                className="h-6 w-6"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"
                                                />
                                            </svg>
                                        </span>

                                        <h1 className="text-xl font-semibold text-gray-700 capitalize dark:text-white">Easy to customiztions</h1>

                                        <p className="text-gray-500 dark:text-gray-300">
                                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Provident ab nulla quod dignissimos vel non
                                            corrupti doloribus voluptatum eveniet
                                        </p>

                                        <a
                                            href="#"
                                            className="-mx-1 inline-flex transform items-center text-sm text-blue-500 capitalize transition-colors duration-300 hover:text-blue-600 hover:underline dark:text-blue-400 dark:hover:text-blue-500"
                                        >
                                            <span className="mx-1">read more</span>
                                            <svg
                                                className="mx-1 h-4 w-4 rtl:-scale-x-100"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z"
                                                    clip-rule="evenodd"
                                                ></path>
                                            </svg>
                                        </a>
                                    </div>

                                    <div className="space-y-3">
                                        <span className="inline-block rounded-full bg-blue-100 p-3 text-blue-500 dark:bg-blue-500 dark:text-white">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                className="h-6 w-6"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"
                                                />
                                            </svg>
                                        </span>

                                        <h1 className="text-xl font-semibold text-gray-700 capitalize dark:text-white">Simple & clean designs</h1>

                                        <p className="text-gray-500 dark:text-gray-300">
                                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Provident ab nulla quod dignissimos vel non
                                            corrupti doloribus voluptatum eveniet
                                        </p>

                                        <a
                                            href="#"
                                            className="-mx-1 inline-flex transform items-center text-sm text-blue-500 capitalize transition-colors duration-300 hover:text-blue-600 hover:underline dark:text-blue-400 dark:hover:text-blue-500"
                                        >
                                            <span className="mx-1">read more</span>
                                            <svg
                                                className="mx-1 h-4 w-4 rtl:-scale-x-100"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z"
                                                    clip-rule="evenodd"
                                                ></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </section>

                    {/* <!--    Upselling--> */}
                    <div className="flex min-h-[66vh] w-full items-center justify-center bg-[var(--secondary-200)]">
                        <p>what we can do for you</p>
                    </div>

                    {/* <!--    Plans--> */}
                    <div className="text w-full bg-[var(--custom-background)] p-4">
                        <div className="container mx-auto px-6 py-8">
                            <div className="xl:-mx-8 xl:flex xl:items-center">
                                <div className="flex flex-col items-center xl:mx-8 xl:items-start">
                                    <h1 className="text-2xl font-medium capitalize lg:text-3xl text">Pricing</h1>

                                    <div className="mt-4">
                                        <span className="inline-block h-1 w-40 rounded-full bg-blue-500"></span>
                                        <span className="mx-1 inline-block h-1 w-3 rounded-full bg-blue-500"></span>
                                        <span className="inline-block h-1 w-1 rounded-full bg-blue-500"></span>
                                    </div>

                                    <p className="mt-4 font-medium">You can get All Access by selecting your plan!</p>

                                    <a href="#" className="link -mx-1 mt-4 flex items-center text-sm text-gray-700 capitalize">
                                        <span className="mx-1">read more</span>
                                        <svg
                                            className="mx-1 h-4 w-4 rtl:-scale-x-100"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z"
                                                clip-rule="evenodd"
                                            ></path>
                                        </svg>
                                    </a>
                                </div>

                                <div className="flex-1 xl:mx-8">
                                    <div className="mt-8 space-y-8 md:-mx-4 md:flex md:items-center md:justify-center md:space-y-0 xl:mt-0">
                                        <div className="mx-auto max-w-sm rounded-lg border md:mx-4 dark:border-gray-700">
                                            <div className="p-6">
                                                <h1 className="text-xl font-medium text-gray-700 capitalize lg:text-2xl dark:text-white">
                                                    Essential
                                                </h1>

                                                <p className="mt-4 text-gray-500 dark:text-gray-300">
                                                    Lorem ipsum dolor sit amet consectetur, adipisicing elit. Nostrum quam voluptatibus
                                                </p>

                                                <h2 className="mt-4 text-2xl font-semibold text-gray-700 sm:text-3xl dark:text-gray-300">
                                                    $3.00 <span className="text-base font-medium">/Month</span>
                                                </h2>

                                                <p className="mt-1 text-gray-500 dark:text-gray-300">Yearly payment</p>

                                                <button className="button primary-button mt-6 w-full transform px-4 py-2 tracking-wide capitalize transition-colors duration-300">
                                                    Start Now
                                                </button>
                                            </div>

                                            <hr className="border-gray-200 dark:border-gray-700" />

                                            <div className="p-6">
                                                <h1 className="text-lg font-medium text-gray-700 capitalize lg:text-xl dark:text-white">
                                                    What’s included:
                                                </h1>

                                                <div className="mt-8 space-y-4">
                                                    <div className="flex items-center">
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            className="h-5 w-5 text-blue-500"
                                                            viewBox="0 0 20 20"
                                                            fill="currentColor"
                                                        >
                                                            <path
                                                                fill-rule="evenodd"
                                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                                clip-rule="evenodd"
                                                            />
                                                        </svg>

                                                        <span className="mx-4 text-gray-700 dark:text-gray-300">All limited links</span>
                                                    </div>

                                                    <div className="flex items-center">
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            className="h-5 w-5 text-blue-500"
                                                            viewBox="0 0 20 20"
                                                            fill="currentColor"
                                                        >
                                                            <path
                                                                fill-rule="evenodd"
                                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                                clip-rule="evenodd"
                                                            />
                                                        </svg>

                                                        <span className="mx-4 text-gray-700 dark:text-gray-300">Own analytics platform</span>
                                                    </div>

                                                    <div className="flex items-center">
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            className="h-5 w-5 text-blue-500"
                                                            viewBox="0 0 20 20"
                                                            fill="currentColor"
                                                        >
                                                            <path
                                                                fill-rule="evenodd"
                                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                                clip-rule="evenodd"
                                                            />
                                                        </svg>

                                                        <span className="mx-4 text-gray-700 dark:text-gray-300">Chat support</span>
                                                    </div>

                                                    <div className="flex items-center">
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            className="h-5 w-5 text-blue-500"
                                                            viewBox="0 0 20 20"
                                                            fill="currentColor"
                                                        >
                                                            <path
                                                                fill-rule="evenodd"
                                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                                clip-rule="evenodd"
                                                            />
                                                        </svg>

                                                        <span className="mx-4 text-gray-700 dark:text-gray-300">Optimize hashtags</span>
                                                    </div>

                                                    <div className="flex items-center">
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            className="h-5 w-5 text-red-400"
                                                            viewBox="0 0 20 20"
                                                            fill="currentColor"
                                                        >
                                                            <path
                                                                fill-rule="evenodd"
                                                                d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z"
                                                                clip-rule="evenodd"
                                                            />
                                                        </svg>

                                                        <span className="mx-4 text-gray-700 dark:text-gray-300">Mobile app</span>
                                                    </div>

                                                    <div className="flex items-center">
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            className="h-5 w-5 text-red-400"
                                                            viewBox="0 0 20 20"
                                                            fill="currentColor"
                                                        >
                                                            <path
                                                                fill-rule="evenodd"
                                                                d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z"
                                                                clip-rule="evenodd"
                                                            />
                                                        </svg>

                                                        <span className="mx-4 text-gray-700 dark:text-gray-300">Unlimited users</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div className="mx-auto max-w-sm rounded-lg border md:mx-4 dark:border-gray-700">
                                            <div className="p-6">
                                                <h1 className="text-xl font-medium text-gray-700 capitalize lg:text-2xl">Premium</h1>

                                                <p className="mt-4 text-gray-500 dark:text-gray-300">
                                                    Lorem ipsum dolor sit amet consectetur, adipisicing elit. Nostrum quam voluptatibus
                                                </p>

                                                <h2 className="mt-4 text-2xl font-semibold text-gray-700 sm:text-3xl">
                                                    $50.00 <span className="text-base font-medium">/life time</span>
                                                </h2>

                                                <p className="mt-1 text-gray-500 dark:text-gray-300">One time payment</p>

                                                <button className="text button primary-button mt-6 w-full transform px-4 py-2 tracking-wide capitalize transition-colors duration-300">
                                                    Start Now
                                                </button>
                                            </div>

                                            <hr className="border-gray-200 dark:border-gray-700" />

                                            <div className="p-6">
                                                <h1 className="text-lg font-medium capitalize lg:text-xl">What’s included:</h1>

                                                <div className="mt-8 space-y-4">
                                                    <div className="flex items-center">
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            className="h-5 w-5 text-blue-500"
                                                            viewBox="0 0 20 20"
                                                            fill="currentColor"
                                                        >
                                                            <path
                                                                fill-rule="evenodd"
                                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                                clip-rule="evenodd"
                                                            />
                                                        </svg>

                                                        <span className="mx-4 text-gray-700 dark:text-gray-300">All limited links</span>
                                                    </div>

                                                    <div className="flex items-center">
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            className="h-5 w-5 text-blue-500"
                                                            viewBox="0 0 20 20"
                                                            fill="currentColor"
                                                        >
                                                            <path
                                                                fill-rule="evenodd"
                                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                                clip-rule="evenodd"
                                                            />
                                                        </svg>

                                                        <span className="mx-4 text-gray-700 dark:text-gray-300">Own analytics platform</span>
                                                    </div>

                                                    <div className="flex items-center">
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            className="h-5 w-5 text-blue-500"
                                                            viewBox="0 0 20 20"
                                                            fill="currentColor"
                                                        >
                                                            <path
                                                                fill-rule="evenodd"
                                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                                clip-rule="evenodd"
                                                            />
                                                        </svg>

                                                        <span className="mx-4 text-gray-700 dark:text-gray-300">Chat support</span>
                                                    </div>

                                                    <div className="flex items-center">
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            className="h-5 w-5 text-blue-500"
                                                            viewBox="0 0 20 20"
                                                            fill="currentColor"
                                                        >
                                                            <path
                                                                fill-rule="evenodd"
                                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                                clip-rule="evenodd"
                                                            />
                                                        </svg>

                                                        <span className="mx-4 text-gray-700 dark:text-gray-300">Optimize hashtags</span>
                                                    </div>

                                                    <div className="flex items-center">
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            className="h-5 w-5 text-blue-500"
                                                            viewBox="0 0 20 20"
                                                            fill="currentColor"
                                                        >
                                                            <path
                                                                fill-rule="evenodd"
                                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                                clip-rule="evenodd"
                                                            />
                                                        </svg>

                                                        <span className="mx-4 text-gray-700 dark:text-gray-300">Mobile app</span>
                                                    </div>

                                                    <div className="flex items-center">
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            className="h-5 w-5 text-blue-500"
                                                            viewBox="0 0 20 20"
                                                            fill="currentColor"
                                                        >
                                                            <path
                                                                fill-rule="evenodd"
                                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                                clip-rule="evenodd"
                                                            />
                                                        </svg>

                                                        <span className="mx-4 text-gray-700 dark:text-gray-300">Unlimited users</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* <!--    Work with us--> */}
                    <section className="bg-[var(--custom-accent)] w-full">
                        <div className="container flex flex-col items-center px-4 py-12 mx-auto text-center">
                            <h2 className="max-w-2xl mx-auto text-2xl font-semibold tracking-tight text-[var(--custom-text)] xl:text-3xl">
                                Take your Business to the <span className="text-[var(--custom-primary)]">next level.</span>
                            </h2>

                            <p className="max-w-4xl mt-6 text-center text-gray-500 dark:text-gray-300">
                                Lorem, ipsum dolor sit amet consectetur
                                adipisicing elit. Cum quidem officiis reprehenderit, aperiam veritatis non, quod
                                veniam fuga possimus hic
                                explicabo laboriosam nam. A tempore totam ipsa nemo adipisci iusto!
                            </p>

                            <div className="inline-flex w-full mt-6 sm:w-auto">
                                <a href="#"
                                   className="inline-flex items-center justify-center w-full button primary-button duration-300">
                                    Sign Up
                                </a>
                            </div>
                        </div>
                    </section>

                    <LandingFooter />
                </div>
            </LandingLayout>
        </>
    );
}
