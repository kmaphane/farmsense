# Farmsense - Integrated Smart Farming Platform

## Project Requirements Document (PRD)

- **Version:** 2.2 (Final Architecture)
- **Project Status:** Ready for Implementation
- **Architecture:** Modular Monolith (Domain-Driven Design)
- **Target Platform:** Web Application (SaaS Ready)
- **Primary Market:** Internal use (Botswana) → Scaling to SaaS for African Farmers

---

## 1. Executive Summary

### 1.1 Project Vision

Farmsense is a modular, scalable farm management ecosystem. While starting with Broiler Chicken Farming, the architecture is designed to accommodate various agricultural verticals (Layers, Hatchery, Livestock) and IoT automation. The ultimate goal is to transition from an internal management tool to a commercial SaaS product.

### 1.2 The "Modular" Strategy

Unlike traditional monoliths, Farmsense treats specific farming types as distinct Domains. The Core system handles shared resources (Money, People, Assets), while specific modules (e.g., BroilerModule) handle the unique logic of that farming type. This ensures that adding "Layers" or "Cattle" later does not destabilize the existing system.

---

## 2. Technical Architecture

### 2.1 Core Stack

- **Backend Framework:** Laravel 12.x
- **Admin/Back-office UI:** Filament PHP v4 (Tables, Forms, Widgets, Notifications)
- **Client/Field UI:** Inertia.js + React 18 + Tailwind CSS (mobile-first field data entry)
- **Database:** PostgreSQL 16+ (utilizing JSONB for flexible IoT data)
- **API Documentation:** Dedoc Scramble (auto-generated OpenAPI/Swagger)

### 2.2 Directory Structure (DDD)

We are moving business logic out of `app/` into a root-level `Domains/` directory to enforce strict boundaries.

```plaintext
farmsense/
├── app/                  # Infrastructure (HTTP, Providers, Filament Panels)
├── bootstrap/
├── Domains/              # ← THE CORE LOGIC
│   ├── Shared/           # (Kernel) Traits, Enums, DTOs, Interfaces
│   ├── Auth/             # Users, Roles (Shield), Tenants (Teams)
│   ├── CRM/              # Customers & Suppliers
│   ├── Finance/          # Invoices, Expenses, Tax, Banking
│   ├── Inventory/        # Stock, Warehouses, Feed Management
│   ├── Broiler/          # (Specific) Batches, Mortality, FCR
│   └── IoT/              # (Future) Sensors, Device Management
├── public/
└── composer.json         # Autoloads "Domains\\" namespace
```

### 2.3 Key Packages & Tools

| Feature | Package | Purpose |
|---------|---------|---------|
| Admin Panel | `filament/filament` | Rapid CRUD development for Admin/Office tasks |
| Permissions | `bezhansalleh/filament-shield` | Robust Role-Based Access Control (RBAC) via GUI |
| Currency | `cknow/laravel-money` | Handles BWP currency as integers (Value Objects) to prevent float errors |
| DTOs | `spatie/laravel-data` | Single Source of Truth for data flow. Prevents "array hell" |
| Feature Flags | `laravel/pennant` | Manage rollout of features per tenant/subscription plan |
| Audit Logs | `rmsramos/activitylog` | Wrapper for spatie/activitylog to track who changed what |
| Static Analysis | `larastan/larastan` | Enforces code quality and modular boundaries |

---

## 3. Core Modules (Phase 1 & 2)

### 3.1 Module: Auth & Tenancy (The SaaS Foundation)

**Tenancy Strategy:** Single database with `team_id` on every critical table.

**Global Scope:** A `BelongsToTeam` trait must be applied to models to automatically filter queries by the logged-in user's team.

**Roles (Filament Shield):**

- **Super Admin:** System owner (You)
- **Farm Manager:** Full access to financial and operational data
- **Partner:** Read-only access to Financials; Read/Write access to Batches
- **Field Worker:** Restricted access. Can only input daily logs (Feed, Mortality). No access to Financials

### 3.2 Module: CRM

- **Suppliers:** Categorized (Feed, Chicks, Meds). Tracks performance ratings.
- **Customers:** Wholesale vs. Retail. Tracks credit limits and payment terms.

### 3.3 Module: Finance

**Invoicing:** PDF generation, Partial Payments, Aging Reports.

**Expenses:**

- **Interface-First OCR:** ReceiptScanner interface
  - Driver A (Dev): OcrSpaceScanner (Free API)
  - Driver B (Prod): GoogleVisionScanner (High accuracy, paid)
- **Cost Centers:** Expenses must be polymorphic (Allocatable) to attach to a specific Batch or the General Farm

### 3.4 Module: Inventory

- **Stock:** Feed bags, Medicine bottles, Packaging
- **Warehouses:** Multi-location support (e.g., "Main Barn", "Feed Store")
- **Movements:** Strict audit trail of Stock In vs. Stock Out

---

## 4. Domain Module: Broiler Farming (The MVP)

### 4.1 Batch Management

- **Concept:** A "Batch" is the aggregate root for a cycle of chickens (e.g., "Batch 104 - Ross 308")
- **Lifecycle:** Planned → Active → Harvesting → Closed
- **Financial Link:** Every bag of feed consumed by a Batch is a Direct Cost in the Finance Module

### 4.2 Field Operations (Frontend: React/Inertia)

**Daily Log:** Mobile-optimized form for workers to enter:

- Mortality (Dead birds)
- Culls (Sick birds removed)
- Feed Intake (Bags used)
- Water Consumption (Liters)

**Analytics:** Real-time calculation of:

- FCR (Feed Conversion Ratio)
- EPEF (European Production Efficiency Factor)

---

## 5. Implementation Roadmap

### Phase 1: The Foundation (Weeks 1-3)

- Setup: Laravel 12 install, Domains/ directory configuration
- Auth & Roles: Install filament-shield. Define Roles (Partner, Worker). Set up Team model
- CRM: Build Customer and Supplier Resources in Filament
- Finance (Part A): Expense recording with the Interface-based OCR service pattern

### Phase 2: Inventory & Logic (Weeks 4-6)

- Inventory: Product Catalog and Stock Movements
- Finance (Part B): Invoicing and Payments
- DTO Implementation: Converting all Form Requests to Spatie\LaravelData objects

### Phase 3: Broiler Domain (Weeks 7-10)

- Batch Engine: The core logic for tracking a flock
- React Frontend: Building the "Field Mode" UI for workers using Inertia.js
- Analytics: Building the Dashboards to visualize FCR and Profitability per Batch

### Phase 4: Polish & Prep (Weeks 11-12)

- Audit Logs: Ensure rmsramos/activitylog is capturing critical events
- Docs: Run dedoc/scramble to generate API documentation
- Deployment: CI/CD pipeline setup

---

## 6. Detailed Data Schemas (Critical Tables)

### `teams` (Tenants)

| Column | Type | Notes |
|--------|------|-------|
| id | PK | |
| name | string | |
| owner_id | FK (User) | |
| subscription_plan | enum | Basic, Pro, Enterprise |

### `expenses`

| Column | Type | Notes |
|--------|------|-------|
| id | PK | |
| team_id | FK | Critical for SaaS |
| amount | BigInt | Stores Cents (Thebe) |
| currency | string | Default 'BWP' |
| category | string | |
| allocatable_type | string | e.g., Domains\Broiler\Models\Batch |
| allocatable_id | int | |
| ocr_data | JSONB | Stores raw data from scanning |
| receipt_path | string | |

### `batches`

| Column | Type | Notes |
|--------|------|-------|
| id | PK | |
| team_id | FK | |
| batch_number | string | |
| breed | string | Cobb 500, Ross 308 |
| initial_quantity | int | |
| current_quantity | int | Derived/Cached count |
| status | enum | Planned, Active, Closed |
| start_date | date | |

---

## 7. Action Items for Developer

- [ ] **Initialize Repository:** Set up git and standard Laravel structure
- [ ] **Refactor Autoload:** Edit composer.json to register the Domains namespace immediately
- [ ] **Install Shield:** Run `php artisan shield:install` immediately after Filament setup to generate the permissions tables
- [ ] **Create OCR Interface:** Define `Domains/Shared/Contracts/ReceiptScanner.php` before writing any OCR logic
