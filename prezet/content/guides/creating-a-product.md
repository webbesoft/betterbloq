---
title: Creating a product
date: 2025-05-23
category: Guides
excerpt: How to create a new product in Betterbloq.
image: /prezet/img/ogimages/features-frontmatter.webp
---

Products are the core of this application. They can be materials, or goods.

Product Creation (by Vendor):

1. A product always belongs to a vendor and a category.
2. Information Entered: name, description, price, unit, category_id, vendor_id, delivery_time, image.
3. Stripe Integration (Automated): On product save, we automatically communicate with the Stripe API to create a Stripe product & price, storing the returned IDs (stripe_product_id, stripe_price_id) on your product record.
4. Storage & Handling Data: All fields like storable, storage_unit_of_measure, dimensions, is_stackable, storage_conditions_required (as an array from CheckboxList), and storage_handling_notes are filled.
5. These products are what users will be able to order and bulk purchase via the purchase pools.
