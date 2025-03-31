import LandingLayout from "@/layouts/landing-layout";
import { PlanType } from "@/types/model-types";
import { Head, useForm } from "@inertiajs/react";
import { useEffect, useState } from "react";

type PlanForm = {
    priceId: string,
    interval: string
}

interface PropsType {
    flash: any;
    plans: PlanType[];
}

export default function Plans (props: PropsType) {
    const { flash, plans } = props;
    
    const [isMonthly, setIsMonthly] = useState<boolean>(true);
    const { post, data, setData } = useForm<Required<PlanForm>>({
        priceId: "",
        interval: ""
    });

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
    }, [data.priceId])

    return (
        <LandingLayout>
            <Head title="Plans & Pricing">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="bg-white py-12 h-screen">
            <div className="container mx-auto max-w-screen-lg px-4">
            <div className="m-4">{flash.message && <div className="alert">{flash.message}</div>}</div>

                <h1 className="text-3xl font-semibold text-center text-gray-800 mb-8">Plans & Pricing</h1>
                <p className="text-center text-gray-600 mb-6 text-sm">Save over 30% when you select annual billing</p>
                <div className="flex justify-center mb-8 border rounded-md overflow-hidden shadow-sm">
                    <button
                        className={`py-2 px-6 font-semibold transition-colors duration-300 ${
                            isMonthly ? 'bg-gray-800 text-white' : 'bg-white text-gray-600 hover:bg-gray-200'
                        }`}
                        onClick={() => handleBillingToggle(true)}
                    >
                        Monthly
                    </button>
                    <button
                        className={`py-2 px-6 font-semibold transition-colors duration-300 ${
                            !isMonthly ? 'bg-gray-800 text-white' : 'bg-white text-gray-600 hover:bg-gray-200'
                        }`}
                        onClick={() => handleBillingToggle(false)}
                    >
                        Annual
                    </button>
                </div>
                <form method="post" onSubmit={(e) => e.preventDefault()} className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {plans.map((plan, index) => (
                        <div
                            key={index}
                            className={`bg-white rounded-lg shadow-md p-6 flex flex-col justify-between ${
                                plan.isPopular ? 'border-2 border-blue-500' : 'border border-gray-200'
                            }`}
                        >
                            {plan.isPopular && (
                                <div className="relative">
                                    <span className="absolute top-[-10px] right-2 bg-yellow-400 text-white text-xs font-bold py-1 px-2 rounded">
                                        most popular !
                                    </span>
                                </div>
                            )}
                            <div>
                                <h3 className="text-xl font-semibold text-gray-800 mb-2">{plan.name}</h3>
                                <p className="text-gray-500 mb-4 text-sm">Free for 7 days then</p>
                                <div className="text-center mb-4">
                                    {/* <span className="text-4xl font-bold text-blue-500">
                                        ${isMonthly ? plan.monthlyPrice : plan.annualPrice}
                                    </span> */}
                                    <span className="text-gray-600 text-sm">${plan.price} / mo</span>
                                </div>
                                <ul className="list-none space-y-2 mb-6">
                                    {/* {plan.features.map((feature, i) => (
                                        <li key={i} className="text-gray-700 text-sm border-b border-gray-300 py-2">
                                            {feature}
                                        </li>
                                    ))} */}
                                </ul>
                            </div>
                            <input type="hidden" value={plan.stripe_plan} name="priceId" />
                            {/* <input type="hidden" value={isMonthly ? 'monthly' : 'yearly'} name="interval" /> */}
                            <button
                                type="submit"
                                onClick={() => handleCheckout(plan)}
                                className="bg-green-500 hover:bg-green-700 text-white font-bold py-3 rounded-md transition duration-300"
                            >
                                Try it free
                            </button>
                        </div>
                    ))}
                </form>
            </div>
        </div>
        </LandingLayout>
    )
}