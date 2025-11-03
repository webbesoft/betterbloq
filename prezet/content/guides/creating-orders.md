---
title: Creating an order
date: 2025-05-18
category: Guides
excerpt: How to create a new order in Betterbloq.
image: /prezet/img/ogimages/features-frontmatter.webp
---

Order Creation Flow:

- Step 1: Select Vendor: The user first selects a Vendor.
- Step 2: Select Products (from selected Vendor): Based on the selected Vendor, the Product selection will be filtered to only show products from that vendor. Users can select one or many products.
- Step 3: Define Quantities & Review: For each selected product, the user will specify the quantity. The system will then calculate price_per_unit, total_price (before discount), applied_discount_percentage, and final_line_price for each line item. A summary of the order will be shown.
- Step 4: Customer & Address Details: Capture phone, email, address, billing_address, shipping_address, user_id.
- Post-Creation Action: After the order is successfully created (including its line items), trigger an event or action to:
    - Initiate a Stripe checkout session.
    - Get the checkout URL.
    - Send the checkout URL to the customer's email.
