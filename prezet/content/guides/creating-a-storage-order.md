---
title: Creating a storage order
date: 2025-05-24
category: Guides
excerpt: How to create a new storage order in Betterbloq.
image: /prezet/img/ogimages/features-frontmatter.webp
---

# Storage Order

A Storage Order tracks the details of products being stored in a warehouse after a purchase. This includes initial requests, actual storage details upon arrival, and billing information.
Creating a Storage Order

The process of creating a Storage Order is broken down into three steps:

## Basic Information

- User: The user associated with this storage order.
- Order: The original purchase order linked to this storage request.
- Storage Warehouse: The warehouse where the product will be stored.
- Requested Storage Start Date: The date the user requested storage to begin.
- Requested Storage Duration Estimate: An estimated duration of storage (e.g., "3 months," "approx 60 days").
- Preliminary Storage Cost Estimate: An initial estimated cost of storage, if provided to the user.

## Actual Storage Details (Post-Arrival)

These fields are typically filled in by an admin once the goods have physically arrived at the warehouse.

- Actual Storage Start Date: The actual date the product began being stored.
- Actual Storage End Date: The actual date the product storage ended.
- Manually Entered Total Space Units: The actual total space units (e.g., square feet, pallets) used by the order.
- Calculated Space Unit Type: The unit type used for billing this order (e.g., "Square Feet (Floor)", "Cubic Meter", "Pallet Space").
- Applied Storage Tier: The specific Storage Tier applied to this order for billing purposes.
- Actual Rate Per Unit Per Period: The actual rate charged per space unit per billing period.
- Billing Period for Actuals: The billing frequency for the actual storage (e.g., "Per Day", "Per Week", "Per Month").
- Next Billing Date: The next scheduled date for storage billing.
- Total Actual Storage Cost to Date: The cumulative cost of storage for this order to date. This is updated automatically via billing cycles and is read-only.

## Status & Notes

- Status: The current status of the storage order:
    - Pending Arrival: The product is expected but not yet at the warehouse.
    - Stored: The product is currently being stored.
    - Partially Retrieved: Some of the product has been retrieved from storage.
    - Retrieved: All of the product has been retrieved from storage.
    - Cancelled: The storage order was cancelled.
- Notes: Any additional notes or comments related to the storage order.
