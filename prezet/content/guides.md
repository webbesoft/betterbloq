---
title: Working with Betterbloq
excerpt: Guides for platform admins.
date: 2025-05-18
category: Getting Started
image: /prezet/img/ogimages/index.png
---

System Setup: Creation Order & Relationships

To effectively utilize the purchase and storage features, you must create certain records before others. This ensures that when you create a new item, any required linked records already exist in the system.

1. User

Prerequisite for: Vendor, Warehouse, Storage Order

Relationship:

    A Vendor must be associated with a User responsible for its management.
    A Warehouse must be associated with a User responsible for its management.
    A Storage Order must be associated with a User who placed or is responsible for the order.

Creation Notes:

    Users are fundamental to assigning responsibility and access within the system. Create your core user accounts first.

2. Product

Prerequisite for: Purchase Pool, Storage Order Line Item

Relationship:

    A Purchase Pool is created for a specific Product.
    A Storage Order Line Item specifies the Product being stored.

Creation Notes:

    Ensure all products that will be part of purchase cycles or require storage are defined in the system. Products are linked to Vendors, so ensure your Vendors are set up first if you wish to associate products with them during creation.

3. Vendor

Prerequisite for: Product (indirectly, as Product has a Vendor relationship)

Relationship:

    A Product is typically supplied by a Vendor. The Vendor's prep_time also influences the target_delivery_date of a Purchase Pool.

Creation Notes:

    Define your vendors before or concurrently with your products, as products will often link back to a vendor.

4. Purchase Cycle

Prerequisite for: Purchase Pool

Relationship:

    A Purchase Pool must be linked to an existing Purchase Cycle.

Creation Notes:

    Before you can group orders into a "pool" for bulk purchasing, you need to define the active or upcoming periods (Purchase Cycles) during which these pools can operate.

5. Warehouse

Prerequisite for: Storage Tier, Storage Order

Relationship:

    A Storage Tier can be optionally linked to a specific Warehouse.
    A Storage Order must specify the Warehouse where items will be stored.

Creation Notes:

    Set up your physical storage locations (warehouses) before defining storage pricing or creating storage orders.

6. Purchase Pool

Prerequisite for: Purchase Pool Tier

Relationship:

    A Purchase Pool Tier belongs to a specific Purchase Pool.

Creation Notes:

    Once you have a Purchase Cycle and a Product defined, you can create a Purchase Pool to begin accumulating orders for that product within a specific cycle.

7. Purchase Pool Tier

Prerequisite for: None (after Purchase Pool)

Relationship:

    Provides discount structures based on Purchase Pool volume.

Creation Notes:

    After creating a Purchase Pool, you can define different Purchase Pool Tiers to offer varying discounts based on the total volume achieved within that pool.

8. Storage Tier

Prerequisite for: Storage Order (specifically, for applying a storage tier)

Relationship:

    A Storage Order can have an applied_storage_tier_id to determine its pricing.

Creation Notes:

    Define your storage pricing models (Storage Tiers) once your Warehouses are set up. This allows the system to calculate estimated and actual storage costs for Storage Orders.

9. Order (External)

Prerequisite for: Storage Order

Relationship:

    A Storage Order is linked to an Order from which the stored products originated.

Creation Notes:

    While not explicitly defined in the provided models, the Storage Order has an order_id field. This implies that the original purchase or sales order (which presumably includes product details) must exist before a storage order can be created for its items.

10. Storage Order

Prerequisite for: Storage Order Line Item

Relationship:

    A Storage Order Line Item belongs to a Storage Order.

Creation Notes:

    Once an Order is placed and products are ready for storage, a Storage Order can be created, linking it to the relevant User, Order, and Warehouse.

11. Storage Order Line Item

Prerequisite for: None (after Storage Order and Product)

Relationship:

    Detailing the specific products and their quantities within a Storage Order.

Creation Notes:

    These are the final granular details for storage, specifying which products, their quantities, and dimensions are part of a Storage Order.
