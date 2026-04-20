

## What **[title]** is for

**[title]** is a **web-based online marketplace** for **handmade / handicraft products**. It connects **local artisans** (sellers) with **customers** (buyers) in one place: shoppers can discover products, add them to a cart, place orders, and coordinate payment and delivery; artisans can publish and manage listings and fulfill orders; **administrators** oversee quality, payments, catalog structure, and user accounts so the marketplace stays consistent and trustworthy.

The system is meant to reduce reliance on informal selling (scattered chats and social posts) by centralizing **catalog, orders, payment proof, messaging, and reviews** in a single application backed by a **relational database**.

---

## Who uses it (roles)

- **Guest / public** — can browse the storefront without logging in (where routes allow).
- **Customer** — registered shopper: cart, checkout, orders, reviews, account/shipping settings, notifications, messaging.
- **Artisan** — registered seller: own products, orders for their shop, artisan profile.
- **Administrator** — platform operator: approve/reject products, verify payments, manage categories and users, record admin (POS-style) sales.

Access is **role-based**: after login, middleware sends each role to the correct dashboards and blocks unauthorized URLs (e.g. customers cannot open admin routes).

---

## Features (by area)

### Public storefront

- **Home** — marketing/landing content and entry to the site.
- **Products** — list and search/browse catalog; **product detail** pages with price, description, stock, images, and link to the artisan.
- **Artisans** — directory of seller profiles; **artisan profile** pages so buyers can learn about the maker before purchasing.

### Accounts and authentication

- **Email/password registration and login** (Laravel-style auth).
- **Google OAuth** — sign-in (or account linking) via Google; user record can store Google id and avatar.
- **Artisan onboarding** — route for **registering as an artisan** or **upgrading** an existing customer account; **pending application** page after submission if the workflow requires approval before full artisan access.

### Customer features

- **Customer dashboard** — overview relevant to the logged-in shopper.
- **Shopping cart** — add products, change quantities, remove lines, clear cart.
- **Checkout** — place an order from the cart (shipping and order data tied to the customer account).
- **Orders** — list and view order details; **upload payment proof** for the order’s payment record; **cancel** an order when the rules allow.
- **Reviews** — after purchase flow, create reviews for products tied to an order (supports moderation/approval in the data model).
- **Account / shipping** — edit **shipping address** (and related fields) used for checkout.
- **Notifications** — in-app notification list for events the system records (e.g. order or approval updates, depending on implementation).
- **Messaging**
  - **Order messages** — threaded messages on a specific **order** (customer ↔ artisan context for that sale).
  - **Direct messages** — **chat** between two users (e.g. customer ↔ artisan) outside a single order thread.

### Artisan features

- **Artisan dashboard** — seller-focused summary.
- **Products (CRUD)** — create, read, update, delete (or deactivate) **own** products: name, description, price, stock, category, multiple **images**, etc.
- **Orders** — list orders that belong to the artisan’s sales; view details; **mark orders complete** when fulfillment is done.
- **Profile** — edit **artisan profile** (workshop/story fields as modeled); optional **profile photo** upload/removal.

### Administrator features

- **Admin dashboard** — operational overview.
- **Product approval** — queues for **pending**, **approved**, and **rejected** listings; open a product for **review**; **approve** or **reject** (updates approval status and visibility rules for the public catalog).
- **Payment verification** — **pending** vs **verified** payment lists; review a payment; **verify** or **reject** (aligns with customer-uploaded proof and order state).
- **User management** — lists of **artisans** and **customers**; **suspend** or **activate** accounts to enforce platform policy.
- **Categories** — maintain product **categories** (resource-style admin CRUD without separate show/create/edit pages where simplified).
- **Sales (admin / POS-style)** — record **sales** not necessarily tied to the online cart (in-person or manual sales), with create/list/detail flows and payment method support as implemented.

---

## How it works (technical, high level)

- **Stack:** **Laravel (PHP)** web application with **Blade** views and server-rendered pages (plus front-end assets as built by the project).
- **Architecture:** **Client–server**. Users use a **web browser**; the browser sends HTTP requests to the **application server**. The app does **not** expose the database directly to the browser.
- **Data:** A **relational database** (e.g. SQLite in development, MySQL/MariaDB in production) stores users, roles, artisan profiles, categories, products, images, carts, orders, order lines, payments, messages, direct messages, reviews, notifications, admin sales, etc. **Eloquent models** represent tables and relationships.
- **Routing:** `routes/web.php` defines **public routes**, **auth routes**, and **middleware groups** for **admin**, **artisan**, and **customer** areas so each role only reaches allowed controllers.
- **Typical order flow (simplified):** customer adds items → **checkout** creates an **order** (and line items) and related **payment** record → customer may **upload payment proof** → **admin** **verifies** (or rejects) payment → **artisan** sees the order and can **complete** it; **messages** and **notifications** can update participants along the way.
- **Catalog governance:** new or edited products go through **approval** so only **admin-approved** items appear in the public catalog according to business rules.

---

## One-paragraph summary (paste-friendly)

**[title]** is a Laravel-based handicraft marketplace where customers browse approved products, manage a cart, check out, and submit payment proof; artisans manage their listings, profiles, and orders; and administrators approve products, verify payments, manage categories and users, and record optional POS sales. Authentication includes email/password and Google OAuth. All core entities live in a relational database, and role-based middleware separates admin, artisan, and customer features in the web application.

---

## Diagram file (optional)

If you use the architecture figure in the same folder, **`system-design-diagram.png`** is a black-and-white **client → Laravel server → database** picture with **Google OAuth** on the side; it matches this description at the structural level, not at the feature-list level.
