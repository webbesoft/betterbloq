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
    name: string;
    stripe_plan: string;
    price: number;
    slug: string;
    isPopular: boolean;
    features: any[];
}