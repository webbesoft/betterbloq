---
title: Getting started with Betterbloq
excerpt: Learn about the how to manage orders.
date: 2025-05-18
category: Getting Started
image: /prezet/img/ogimages/index.png
---

This section will give you a foundational understanding of the Betterbloq platform.

A. Welcome to Betterbloq!

1. What is this Application?
    - A Just-In-Time (JIT) bulk purchasing platform for building materials.
2. Who Uses This Documentation?
    - These docs are for internal use.
3. Core Technologies Overview:
   Laravel: The PHP framework the application is built upon.
   Filament: The admin panel/dashboard interface used for managing the platform.
   Stripe: The payment processing platform used for handling orders and product creation.
   B. Accessing the System: The Filament Dashboard
4. Eligibility for Access:
   Only users with an "@mysite.com" email address can access the Filament dashboard.
5. Login Procedure:
   Step-by-step instructions on how to log in (URL, credentials, any two-factor authentication if applicable).
   C. Core Concepts & Terminology
   A glossary or brief explanation of key terms and entities they will encounter:
   User: The base entity. Can be a general customer, or extended to be a Vendor or Warehouse.
   Vendor: A supplier of building materials. Must be linked to a User account and have address information.
   Warehouse: A facility for storing materials. Must be linked to a User account, have address information, and defined conditions (e.g., refrigerated, dry).
   Product: Building materials available for purchase. Automatically created in Stripe upon creation in the system.
   Order: A customer's request to purchase material(s). Linked to Stripe for payment processing.
   Order Line Item: Individual items within an Order. Each Order Line Item must belong to a Purchase Pool.
   Purchase Pool: A time-bound collection of orders for a specific product, designed to achieve bulk discount tiers.
   Purchase Pool Tier: Defines discount levels based on the collective volume of orders within a Purchase Pool.
   Storage Order: Created when an Order's requested delivery date is significantly later than the Purchase Pool's expected delivery date, necessitating storage.
   Storage Order Line Item: Individual items from an Order that are designated for storage.
   Storage Tier: Defines pricing and capacity for storing materials in a Warehouse, based on space required and conditions.
   Payment Intent (Stripe): Created when an order is made; finalized manually via a Filament action when the purchase pool closes.
   D. Navigating the Filament Dashboard
   (Referencing image_5e36a0.png which shows the sidebar navigation)
6. Main Navigation Sections:
   General: Dashboard, Addresses, Categories, Logs, Products.
   Order Management: Orders, Purchase Pools, Purchase Pool Templates, Purchase Pool Tiers.
   Plans Management: Plan Features, Plan Limits, Plans (Note: You haven't detailed "Plans" much, so you might want to expand on this or omit if not immediately relevant for initial support tasks).
   Storage Management: Storage Order Line Items, Storage Orders, Storage Tiers.
7. Common Actions & UI Elements:
   Brief overview of how to find create buttons, lists, edit actions, filters, etc., within Filament.
