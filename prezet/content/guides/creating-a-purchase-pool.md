---
title: Creating a purchase pool
date: 2025-05-24
category: Guides
excerpt: How to create a new purchase pool in Betterbloq.
image: /prezet/img/ogimages/features-frontmatter.webp
---

# Purchase Pool

A Purchase Pool allows you to group individual customer orders for a specific product within an active or upcoming purchase cycle. This enables you to manage minimum order requirements for discounts and set maximum order limits.
Creating a Purchase Pool

To create a Purchase Pool, you'll need to define the following:

- Name: A unique name for the purchase pool.
- Purchase Cycle: Link this pool to an existing upcoming or active Purchase Cycle.
- Target Delivery Date: This date is automatically calculated based on the associated Purchase Cycle's end date, plus vendor preparation time and product delivery time. You cannot manually change this.
- Minimum Orders for Discount: The minimum number of individual orders required within this pool for a discount to be applied.
- Maximum Orders: The maximum number of individual orders allowed in this purchase pool. Set to 0 for no limit.
- Cycle Status: The current status of the purchase pool:
    - Accumulating: Orders are currently being collected.
    - Finalized: The pool has reached its end date, and orders are being processed.
    - Failed: The pool somehow failed to capture the payment intents.
- Target Volume: The desired total volume (e.g., quantity of product) for this purchase pool.
- Current Volume: The current accumulated volume of orders within this pool. This field is read-only and automatically updated.
- Product: The specific product associated with this purchase pool. The vendor for the product will be automatically linked.
