---
title: Creating an order's line items
date: 2025-05-18
category: Guides
excerpt: How to create line items for orders in Betterbloq.
image: /prezet/img/ogimages/features-frontmatter.webp
---

# Order Line Item

An Order Line Item represents a single product entry within an Order, detailing the specific product, its quantity, and pricing information. It acts as a bridge between a specific Order and the Product(s) purchased, potentially reflecting a Purchase Pool's pricing.

Order Line Item Fields

- Order ID: Links this line item to its parent Order.
- Product ID: Links this line item to the specific Product being ordered.
- purchase pool ID: (Optional) Links this line item to a Purchase Pool if the order was part of one. This is crucial for applying Purchase Pool Tier discounts.
- price_per_unit: The base price of a single unit of the product at the time of the order. This might be derived from the product's default price or a tier-specific price.
- total_price: The total price for this line item before any discounts are applied (i.e., price_per_unit \* quantity).
- applied_discount_percentage: The percentage discount applied to this specific line item, possibly derived from a Purchase Pool Tier.
- final_line_price: The final price for this line item after the discount has been applied.
- quantity: The quantity of the specific Product in this line item.

Usage

Order Line Items provide the detailed breakdown of an Order. They are essential for:

- Recording the exact products and quantities purchased.
- Applying and tracking discounts from Purchase Pool Tiers.
- Calculating the final cost of the order.
- Inventory management (deducting ordered quantities from stock).
