import { Product } from "./model-types";

export interface CartItem extends Product {
  quantity: number;
  vendor_id: number;
  vendor_name: string;
}

export interface CartState {
  items: CartItem[];
  currentVendorId: number | string | null;
  currentVendorName: string | null;
  expectedDeliveryDate: Date | null;
}

export interface CartActions {
  addItem: (product: Product) => void;
  removeItem: (productId: number | string) => void;
  updateQuantity: (productId: number | string, quantity: number) => void;
  clearCart: () => void;
  _recalculateDeliveryDate: () => void; // Internal helper, underscore convention
  // You might add more actions like increment/decrement quantity later
}