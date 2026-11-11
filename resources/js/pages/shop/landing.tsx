import '@/../css/betterbloq-home.css';
import LandingLayout from '@/layouts/landing-layout';
import { SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowDown, ArrowRight, ArrowUpRight, Plus } from 'lucide-react';

const steps = [
    {
        title: 'Choose what your build needs.',
        text: 'Browse materials and compare suppliers in the market. Find what fits your project and your budget.',
        note: 'Materials first',
    },
    {
        title: 'Put your order with the others.',
        text: 'Join a purchase cycle. We combine your order with other builders’ orders to unlock volume pricing.',
        note: 'One shared purchase cycle',
    },
    {
        title: 'Get your share of the savings.',
        text: 'Pay the upfront price. When the cycle is finalized, any unlocked volume discount comes back to you as a refund.',
        note: 'Volume discounts refunded',
    },
];

export default function Landing() {
    const { auth } = usePage<SharedData>().props;

    return (
        <LandingLayout breadcrumbs={[]} className="betterbloq-home">
            <Head title="Building materials. Greater buying power.">
                <meta
                    name="description"
                    content="Find building materials and buy alongside other builders. BetterBloq combines orders in shared purchase cycles to unlock volume pricing."
                />
            </Head>

            <section className="bb-hero" aria-labelledby="hero-heading">
                <div className="shop-container">
                    <div className="bb-hero-label">
                        <span>Building materials. Greater buying power.</span>
                        <span aria-hidden="true">Better together / Betterbloq</span>
                    </div>
                    <h1 id="hero-heading">
                        Build more.
                        <br />
                        Buy together<span className="bb-stop">.</span>
                    </h1>
                    <div className="bb-hero-bottom">
                        <a href="#how-it-works" className="bb-how-link">
                            <ArrowDown aria-hidden="true" />
                            <span>A stronger way to buy</span>
                        </a>
                        <div className="bb-hero-intro">
                            <p>
                                Bricks. Cement. The next floor up. Whatever you’re building, put more buying power behind it by ordering alongside
                                other builders.
                            </p>
                            <Link href={route('market')} className="bb-button">
                                Find your materials <ArrowUpRight aria-hidden="true" />
                            </Link>
                        </div>
                    </div>
                </div>
                <div className="bb-materials" aria-label="From foundations to finishes">
                    <div className="bb-material bb-material-brick">
                        <div className="bb-texture bb-brickwork" aria-hidden="true" />
                        <span>
                            <span>01 /</span> Brick by brick
                        </span>
                    </div>
                    <div className="bb-material bb-material-concrete">
                        <div className="bb-texture bb-concrete" aria-hidden="true" />
                        <span>
                            <span>02 /</span> On solid ground
                        </span>
                    </div>
                    <div className="bb-material bb-material-steel">
                        <div className="bb-texture bb-steel" aria-hidden="true" />
                        <span>
                            <span>03 /</span> Built for strength
                        </span>
                    </div>
                    <div className="bb-material bb-material-timber">
                        <div className="bb-texture bb-timber" aria-hidden="true" />
                        <span>
                            <span>04 /</span> Room to grow
                        </span>
                    </div>
                </div>
            </section>

            <section id="how-it-works" className="shop-container bb-process" aria-labelledby="process-heading">
                <div className="bb-process-heading">
                    <p className="bb-label">The buying power of more people</p>
                    <h2 id="process-heading">
                        Your own project.
                        <br />A bigger order.
                    </h2>
                    <p className="bb-process-intro">
                        You don’t need a big development to buy with the crowd. Betterbloq brings individual orders together in shared purchase
                        cycles.
                    </p>
                    <Link href={route('market')} className="bb-text-link">
                        Explore the market <ArrowUpRight aria-hidden="true" />
                    </Link>
                </div>
                <ol className="bb-steps">
                    {steps.map(({ title, text, note }, index) => (
                        <li key={title}>
                            <span className="bb-step-number">0{index + 1}</span>
                            <div>
                                <h3>{title}</h3>
                                <p>{text}</p>
                                <span className="bb-step-note">{note}</span>
                            </div>
                        </li>
                    ))}
                </ol>
            </section>

            <section className="bb-collective" aria-labelledby="collective-heading">
                <div className="shop-container bb-collective-inner">
                    <div className="bb-order-stack" aria-hidden="true">
                        <div>
                            <span>Your build</span>
                            <Plus />
                        </div>
                        <div>
                            <span>Another builder’s order</span>
                            <Plus />
                        </div>
                        <div>
                            <span>And the next one</span>
                            <Plus />
                        </div>
                        <div className="bb-combined-order">
                            <span>Greater buying power</span>
                            <ArrowUpRight />
                        </div>
                    </div>
                    <div className="bb-collective-copy">
                        <p className="bb-label">Small orders add up.</p>
                        <h2 id="collective-heading">
                            There’s strength
                            <br />
                            in numbers.
                        </h2>
                        <p>
                            Same project. More people on your side. Start with the materials you need and let a shared purchase cycle do the heavy
                            lifting.
                        </p>
                        <Link href={auth?.user ? route('dashboard') : route('register')} className="bb-button bb-button-orange">
                            {auth?.user ? 'Open your workspace' : 'Join Betterbloq'} <ArrowRight aria-hidden="true" />
                        </Link>
                        <p className="bb-collective-note">Clear upfront prices. Any unlocked discount refunded at the end of the cycle.</p>
                    </div>
                </div>
            </section>
        </LandingLayout>
    );
}
