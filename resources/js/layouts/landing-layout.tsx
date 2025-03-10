import { AppShell } from '@/components/app-shell';
import { type BreadcrumbItem } from '@/types';
import { type ReactNode } from 'react';

interface LandingLayoutProps {
    children: ReactNode;
    breadcrumbs?: BreadcrumbItem[];
}

export default ({ children }: LandingLayoutProps) => <AppShell>{children}</AppShell>;
