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
