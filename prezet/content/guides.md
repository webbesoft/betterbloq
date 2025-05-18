---
title: Working with Betterbloq
excerpt: Guides for platform admins.
date: 2025-05-18
category: Getting Started
image: /prezet/img/ogimages/index.png
---

Guides: Core Platform Processes

This section will provide step-by-step instructions for common tasks support staff will need to perform.

A. User Management

1. Understanding User Types:
   Detailed explanation of General Users, Vendors, and Warehouses and their roles.
2. Registering New Users (Customers):
   Current process: Users sign up manually (details on how/where if applicable, or if this is outside Filament).
   Future enhancements: Stripe integration for users.
3. Creating and Managing Vendor Accounts:
   Locating the "Vendors" section in Filament.
   Step-by-step:
   Ensuring a base User account exists for the Vendor.
   Creating the Vendor entity.
   Linking the Vendor entity to the corresponding User account.
   Adding and updating address information for the Vendor.
4. Creating and Managing Warehouse Accounts:
   Locating the "Warehouses" section in Filament.
   Step-by-step:
   Ensuring a base User account exists for the Warehouse.
   Creating the Warehouse entity.
   Linking the Warehouse entity to the corresponding User account.
   Adding and updating address information for the Warehouse.
   Defining Warehouse Conditions:
   Explaining the Key-Value pair system (e.g., condition: refrigerated, environment: dry).
   How to add/edit these conditions in the Filament interface.
   B. Product Management
5. Creating a New Product:
   Locating the "Products" section in Filament (referencing image_5e36a0.png).
   Step-by-step:
   Entering product details (name, description, SKU, category, vendor information if linked directly here, pricing information that feeds into Stripe).
   Confirmation of automatic Product creation in Stripe.
6. Viewing and Editing Existing Products:
   How to find and modify product details.
   C. Purchase Pool Management
7. Understanding Purchase Pools:
   Purpose: Consolidate demand for a single product to achieve volume discounts.
   Key characteristics: Product-specific, time-bound (start/end dates), tiered discounts.
   Relationship: Order Line Items belong to Purchase Pools.
8. Creating a Single Purchase Pool:
   (Referencing image_5e3967.png - "Create Purchase Pool" form)
   Accessing from "Purchase Pools" in Order Management (image_5e36a0.png).
   Step-by-step filling out the form:
   Name: Descriptive name for the pool.
   Start Date: When the pool opens for orders.
   End Date: When the pool closes.
   Calculated Target Delivery Date: System calculated (End Date + Vendor Prep Time + Product Delivery Time). Explain where Vendor Prep Time and Product Delivery Time are set/managed.
   Min orders for discount: Minimum number of orders to activate the first discount tier.
   Max orders: Maximum orders allowed in the pool (if applicable).
   Status: (e.g., Upcoming, Active, Closed, Fulfilled).
   Target volume: The desired total quantity of the product.
   Current volume: Automatically updated as orders come in.
   Product: Selecting the specific product for this pool (vendor will be associated automatically).
9. Bulk Creating Purchase Pools:
   Accessing this action (likely from the "Orders" page, as mentioned).
   Step-by-step:
   Selecting multiple products.
   Setting common Start and End Dates.
   Important Note: Emphasize that details like target volumes, discount tiers, etc., need to be manually configured after bulk creation by editing each individual Purchase Pool.
10. Managing Purchase Pool Tiers:
    Accessing "Purchase Pool Tiers" in Order Management (image_5e36a0.png).
    Defining discount tiers for a purchase pool:
    Setting volume thresholds (e.g., 1-50 units, 51-100 units).
    Setting the corresponding discount percentage or amount for each tier.
    D. Order Management
11. Understanding the Order Lifecycle:
    Order creation (by user or manually).
    Payment Intent creation in Stripe (status: requires manual finalization).
    Order Line Items added to relevant Purchase Pools.
    Purchase Pool closure.
    Manual finalization of Payment Intents (Filament action).
    Potential for Storage Order creation.
12. Creating an Order Manually in Filament:
    Accessing "Orders" in Order Management (image_5e36a0.png).
    Step-by-step:
    Selecting the User (customer) placing the order.
    Adding Order Line Items:
    Selecting the Product.
    Specifying quantity.
    Crucial: Assigning each Order Line Item to an active Purchase Pool for that product.
    Specifying the User's Desired Delivery Date.
    Noting that a Payment Intent is created in Stripe.
13. Viewing and Managing Orders:
    How to find orders (search, filter by status).
    Viewing order details, associated line items, and payment status.
14. Finalizing Payments:
    Explaining the process: After a Purchase Pool closes and quantities/discounts are confirmed.
    Locating the Filament action (likely on the Purchase Pool or Order detail page) to finalize the Stripe Payment Intent.
    E. Storage Order Management
15. Identifying the Need for Storage:
    Rule: If a user-supplied delivery date for an Order is more than 3 days after the Purchase Pool's calculated expected delivery date.
16. Creating a Storage Order:
    (Referencing image_5e3a04.png - "Create Storage Order" form)
    Accessing "Storage Orders" in Storage Management (image_5e36a0.png).
    Process is typically initiated after an Order is placed and the need for storage is identified.
    Step-by-step (following the multi-step form):
    01 Basic Information:
    User: Select the user associated with the original order.
    Order: Link to the original purchase Order.
    Storage Warehouse: Select an appropriate Warehouse (considering conditions if applicable).
    Requested storage start date: When storage should begin.
    Requested storage duration estimate: (e.g., "3 months", "approx 60 days").
    Preliminary storage cost estimate: Initial estimate shown to user (if applicable before formal quote).
    02 Actual Storage Details (Post-Arrival):
    (This step implies details are filled in once items arrive at the warehouse. Specify what information is captured here - e.g., confirmed quantity, actual space used, specific location/bay in warehouse, condition check.)
    03 Status & Notes:
    Updating the status of the storage order (e.g., Pending Arrival, Storing, Dispatched, Completed).
    Adding any relevant notes.
17. Managing Storage Order Line Items:
    Accessing "Storage Order Line Items" (image_5e36a0.png).
    Viewing/confirming which specific items from an Order are being stored.
18. Managing Storage Tiers:
    (Referencing image_5e3a5e.png - "Create Storage Tier" form)
    Accessing "Storage Tiers" in Storage Management (image_5e36a0.png).
    Purpose: Define pricing and capacity units for storage within Warehouses.
    Creating/Editing a Storage Tier:
    Name: Descriptive name for the tier (e.g., "Small Pallet Refrigerated", "Bulk Dry Goods Shelf").
    Warehouse: Optionally link this tier to a specific warehouse.
    Tier Limits & Pricing:
    Min space units: Minimum space for this tier (e.g., 1 pallet, 10 sq ft).
    Max space units: Maximum space (leave blank for no upper limit).
    Price per space unit: Cost per defined unit (e.g., per pallet, per sq ft).
    Billing period: (e.g., Daily, Weekly, Monthly).
    Duration:
    Min duration: Minimum storage duration for this tier (e.g., 1 month).
    Duration unit: (e.g., Day(s), Month(s)).
    Conditions (JSON):
    Key-Value pairs defining specific requirements or attributes for this storage tier.
    Examples: {"product_category": "lumber", "requires_climate_control": true}
    Explain that Product.storage_unit_of_measure will define the unit for space calculations.
    How to add/edit these JSON conditions.
