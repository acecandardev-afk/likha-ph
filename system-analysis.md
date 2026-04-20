# System Analysis — Likha PH (Guihulngan Handicrafts)

## Overview

The proposed system, entitled **Likha PH: Guihulngan Handicrafts Online Marketplace**, is a web-based e-commerce platform designed to support local artisans and customers by digitizing product discovery, ordering, payment coordination, and seller–buyer communication. It targets the inefficiencies common in informal and semi-manual selling of handmade goods—such as reliance on scattered social media posts, informal messaging for orders, duplicated product information, and limited visibility into stock, order status, and payment confirmation.

In many existing practices, artisan catalogs exist only in photos or chat threads, making it difficult for buyers to compare items, verify availability, or complete purchases through a single, consistent flow. Order details and shipping addresses are often retyped across channels, which increases errors and delays. Payment proof and fulfillment status are typically tracked outside any shared system, so artisans and administrators lack a unified view of transactions. Product quality and trust signals (such as structured reviews and moderated listings) are hard to maintain without a central repository.

Another limitation is the **absence of an integrated environment** that connects browsing, cart and checkout, order management, payment verification, and communication. When listings, conversations, and payments live in separate tools, information becomes fragmented, follow-up is slower, and generating reliable summaries of sales or inventory for planning is labor-intensive.

To address these gaps, **Likha PH** introduces a single marketplace application where **products, users, orders, payments, messages, and notifications** are stored in a structured relational database and exposed through role-specific interfaces. Artisans manage their own listings and orders; customers browse approved products, use a shopping cart, check out, upload payment proof, leave reviews, and exchange messages with sellers; administrators moderate product submissions, verify payments, manage categories, oversee users (including suspension), and record **point-of-sale** transactions where applicable.

The system adopts a **role-based access** model: **administrators** govern approvals, payments, categories, users, and sales; **artisans** maintain profiles and products and complete orders assigned to them; **customers** shop, pay, and review. Authentication supports standard registration and **Google OAuth**, and user accounts carry shipping details used consistently at checkout.

From a **technical** perspective, the application follows a **client–server architecture**: users interact through a **web browser**, while the **Laravel** application server handles business logic, authorization, and persistence via a **relational database** (for example SQLite or MySQL in deployment). This centralizes data, streamlines workflows, and allows multiple users to use the system concurrently with clear separation of concerns between presentation, application, and data layers.

---

*This analysis reflects the implementation in the Guihulngan Handicrafts / Likha PH codebase (Laravel, product approval workflow, cart and checkout, payment verification, messaging, reviews, notifications, and admin sales module).*
