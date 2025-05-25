export interface Product {
    description: string;
    id: number;
    name: string;
    image: string;
    price: number;
    unit: string;
    vendor: Vendor;
    category: string;
    preparation_time?: number;
    additional_images: any[];
    average_rating: number;
    ratings_count: number;
}

export interface UserRating {
    rating: number;
    comment: string;
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
    limits: any[];
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

export interface Order {
    id: number;
    name: string;
    email: string;
    phone: string;
    address: Address;
    product_id: number;
    user_id: number;
    status: string;
    deleted_at: string | null;
    line_items: OrderLineItem[];
    vendor?: Vendor;
    total_order_price: number;
}

export interface Address {
    address_line_1: string;
    address_line_2: string;
    provice: string;
    postal_code: string;
    city: string;
    country: string;
}

export interface OrderLineItem {
    id: number;
    order_id: number;
    product_id: number;
    quantity: number;
    price_per_unit: number;
    total_price: number;
    product: Product;
    purchase_pool: PurchasePool;
}

export interface PurchasePool {
    cycle_status: string;
    target_volume: string;
    current_volume: string;
    target_delivery_date: Date;
    min_orders_for_discount: number;
    discount_percentage: number;
    max_orders: number;
    created_at: Date;
    updated_at: Date;
    id: number;
    name: string;
    purchase_cycle_id: number;
}
