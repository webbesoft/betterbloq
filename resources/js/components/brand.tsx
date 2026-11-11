import { cn } from '@/lib/utils';

export function Brand({ className, compact = false }: { className?: string; compact?: boolean }) {
    return (
        <span className={cn('inline-flex items-center gap-2.5 group-data-[collapsible=icon]:gap-0', className)}>
            <span className={cn('relative block shrink-0 overflow-hidden', compact ? 'h-6 w-8' : 'h-8 w-12')} aria-hidden="true">
                <img
                    src="/images/logo.png"
                    alt=""
                    className={cn('absolute max-w-none dark:brightness-150 dark:invert', compact ? '-top-4 -left-6 w-20' : '-top-6 -left-8 w-28')}
                />
            </span>
            <span className={cn('font-semibold tracking-[-0.055em] group-data-[collapsible=icon]:hidden', compact ? 'text-xl' : 'text-2xl')}>
                betterbloq<span className="text-primary">.</span>
            </span>
        </span>
    );
}
