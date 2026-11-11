import { Brand } from '@/components/brand';
import { Link } from '@inertiajs/react';
import { ArrowUpRight } from 'lucide-react';
import { type PropsWithChildren } from 'react';

interface AuthLayoutProps {
    title?: string;
    description?: string;
}

export default function AuthSplitLayout({ children, title, description }: PropsWithChildren<AuthLayoutProps>) {
    return (
        <div className="bb-auth grid min-h-dvh lg:grid-cols-2">
            <aside className="bb-auth-story hidden flex-col justify-between p-10 lg:flex xl:p-16">
                <Link href={route('landing')} className="w-fit" aria-label="Betterbloq home">
                    <Brand />
                </Link>
                <div className="my-16">
                    <p className="font-mono text-[10px] font-semibold tracking-wider uppercase">Building materials. Greater buying power.</p>
                    <h2 className="mt-7 text-[clamp(3.5rem,5.5vw,6rem)] leading-[0.96] font-semibold tracking-[-0.065em]">
                        Your next build
                        <br />
                        starts here.
                    </h2>
                    <p className="mt-7 max-w-xs text-base leading-7">
                        Find your materials. Order alongside other builders. Put greater buying power behind your project.
                    </p>
                    <div className="bb-auth-blocks mt-12" aria-hidden="true">
                        <span />
                        <span />
                        <span />
                    </div>
                </div>
                <Link href={route('market')} className="flex w-fit items-center gap-8 border-b border-current pb-2 text-sm font-medium">
                    Take a look around the market <ArrowUpRight className="size-4" />
                </Link>
            </aside>
            <div className="flex items-center px-6 py-12 sm:px-10 lg:px-12">
                <div className="mx-auto flex w-full max-w-sm flex-col gap-7">
                    <Link href={route('landing')} className="mb-5 w-fit lg:hidden" aria-label="Betterbloq home">
                        <Brand />
                    </Link>
                    <div className="border-primary border-t-2 pt-6">
                        <p className="page-eyebrow mb-4">Your Betterbloq account</p>
                        <h1 className="text-3xl leading-tight font-semibold tracking-[-0.05em]">{title}</h1>
                        <p className="text-muted-foreground mt-3 text-sm leading-6">{description}</p>
                    </div>
                    {children}
                </div>
            </div>
        </div>
    );
}
