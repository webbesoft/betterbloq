export interface Product {
    id: number;
    name: string;
    image: string;
    price: number;
    unit: string;
    vendor: Vendor;
    category: string;
}

export interface Vendor {
    id: number;
    name: string;
}

export interface PaginationType {
    current_page: number;
    last_page: number;
    links: PaginationLinks[];
    per_page: number;
    total: number;
}

export interface PaginationBaseLinks {
    first: string;
    last: string;
    next: string;
    prev: string;
}

export interface PaginationLinks {
    active: boolean;
    label: string;
    url: string;
}

export interface PlanType {
    description: ReactNode;
    recommended: any;
    billingNote: any;
    ctaText: ReactNode;
    name: string;
    stripe_plan: string;
    price: number;
    slug: string;
    isPopular: boolean;
    features: any[];
}

export interface Project {
    id: int;
    name: string;
    budget: number;
    start_date: Date;
    target_completion_date: Date;
}

interface ProjectBudgetSpent {
    id: number;
    name: string;
    budget: number;
    total_expenses: number;
}

interface PurchasePoolCompletion {
    id: number;
    name: string;
    target_amount: number;
    current_amount: number;
}

interface WatchedPurchasePool {
    id: number;
    name: string;
    // Add other properties of your PurchasePool model as needed
}

interface FrequentProduct {
    product_id: number;
    name: string;
    frequency: number;
}

interface RegularVendor {
    vendor_id: number;
    name: string;
    frequency: number;
}

interface DashboardProps {
    ongoingProjectsCount: number | null;
    totalExpenses: number | null;
    projectBudgetSpent: ProjectBudgetSpent[];
    purchasePoolCompletion: PurchasePoolCompletion[];
    watchedPurchasePools: WatchedPurchasePool[];
    frequentProducts: FrequentProduct[];
    regularVendors: RegularVendor[];
}
