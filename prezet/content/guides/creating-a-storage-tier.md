---
title: Creating a storage tier
date: 2025-05-24
category: Guides
excerpt: How to create a new storage tier in Betterbloq.
image: /prezet/img/ogimages/features-frontmatter.webp
---

# Storage Tier

Storage Tiers define pricing and conditions for storing products in warehouses. They allow for differentiated pricing based on space, duration, and specific product requirements.
Creating a Storage Tier

When creating a Storage Tier, you'll configure the following:

- Name: A descriptive name for the storage tier.
- Warehouse: Optionally, link this tier to a specific warehouse. If left blank, it can apply across multiple warehouses.
- Tier Limits & Pricing:
    - Minimum Space Units: The minimum space (e.g., square feet, pallets) required for this tier.
    - Maximum Space Units: The maximum space for this tier. Leave blank for no upper limit.
    - Price Per Space Unit: The price charged per defined space unit for this tier.
    - Billing Period: How frequently the storage will be billed (e.g., "Per Day", "Per Week", "Per Month", "Per Quarter", "Per Year").
- Duration:
    - Minimum Duration: The minimum storage duration for this tier.
    - Duration Unit: The unit for the minimum duration (e.g., "Day(s)", "Week(s)", "Month(s)").
- Conditions: JSON-formatted conditions that specify requirements for this tier to apply (e.g., {"product_category": "lumber"} or {"requires_climate_control": true}). The product's storage_unit_of_measure typically defines the unit for space calculations.
- Notes: Any additional notes or information about this storage tier.
