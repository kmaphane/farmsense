# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Initial Setup
```bash
composer setup
```
Runs the full setup: installs dependencies, creates .env file, generates app key, runs migrations, and builds frontend assets.

### Development Server
```bash
composer dev
```
Starts three concurrent processes:
- Laravel development server on `http://localhost:8000`
- Queue worker (database driver)
- Vite dev server with HMR

**SSR Development:**
```bash
composer dev:ssr
```
Starts four concurrent processes: server, queue, logs (Pail), and Inertia SSR server on `http://127.0.0.1:13714`.

### Testing
```bash
composer test
# or directly:
php artisan test
```
Runs Pest test suite. Tests use SQLite in-memory database with array cache/session drivers.

### Frontend Commands
```bash
npm run dev           # Start Vite dev server
npm run build         # Build production assets
npm run build:ssr     # Build both client and SSR bundles
npm run lint          # Run ESLint with auto-fix
npm run format        # Format code with Prettier
npm run format:check  # Check code formatting
npm run types         # TypeScript type checking (no emit)
```

### Code Quality
```bash
./vendor/bin/pint     # Format PHP code (Laravel Pint)
```

### Filament Admin Commands

```bash
php artisan make:filament-resource [ModelName]     # Create a new admin resource for a model
php artisan make:filament-page [PageName]          # Create a custom admin page
php artisan make:filament-widget [WidgetName]      # Create a dashboard widget
php artisan filament:cache-components              # Cache component registrations
php artisan icons:cache                            # Cache Blade icon manifest
php artisan filament:clear-cache                   # Clear all Filament caches
```

## Architecture Overview

### Project Vision
Farmsense is a modular, scalable farm management ecosystem using **Domain-Driven Design (DDD)**. It starts with Broiler Chicken Farming but is designed to accommodate various agricultural verticals (Layers, Hatchery, Livestock) and IoT automation. The ultimate goal is to transition from an internal management tool to a commercial SaaS product.

### Tech Stack
- **Backend:** Laravel 12 (PHP 8.2+)
- **Frontend:** React 19 with TypeScript
- **Bridge:** Inertia.js 2.0 (SPA-like experience with server-side routing)
- **Admin Panel:** Filament PHP 4.2 (Livewire-based admin interface)
- **Database:** PostgreSQL 16+ (utilizing JSONB for flexible IoT data)
- **Auth:** Laravel Fortify (password reset backend) + Filament (admin login UI)
- **Build Tool:** Vite 7 with React Compiler (babel-plugin-react-compiler)
- **UI Frontend:** shadcn/ui components (Radix UI primitives + Tailwind CSS 4)
- **UI Admin:** Filament components (built-in UI system)
- **Routing:** Laravel Wayfinder (auto-generates type-safe route helpers from Laravel routes)

### Domain-Driven Design (DDD) Architecture

**Core Strategy:** Business logic is organized in a root-level `Domains/` directory, separate from infrastructure in `app/`. This enforces strict boundaries and ensures adding new agricultural domains (e.g., Layers, Livestock) doesn't destabilize the existing system.

**Directory Structure:**
```plaintext
farmsense/
├── app/                       # Infrastructure (HTTP, Providers, Filament)
│   ├── Providers/
│   ├── Filament/
│   ├── Models/
│   ├── Http/
│   └── Actions/
├── Domains/                   # ← CORE BUSINESS LOGIC
│   ├── Shared/                # Traits, Enums, DTOs, Interfaces, Contracts
│   ├── Auth/                  # Users, Roles, Tenants (Teams)
│   ├── CRM/                   # Customers & Suppliers
│   ├── Finance/               # Invoices, Expenses, Tax
│   ├── Inventory/             # Stock, Warehouses, Feed Management
│   ├── Broiler/               # (MVP) Batches, Mortality, FCR calculations
│   └── IoT/                   # (Future) Sensors, Device Management
├── composer.json              # Autoloads "Domains\\" namespace
└── ...
```

**Key Packages & Tools:**

| Feature | Package | Purpose |
|---------|---------|---------|
| Admin Panel | `filament/filament` v4+ | Rapid CRUD development for Admin/Office tasks |
| Permissions/RBAC | `bezhansalleh/filament-shield` | Role-Based Access Control via Filament GUI |
| Currency | `cknow/laravel-money` | Handles BWP currency as integers (Value Objects) |
| DTOs | `spatie/laravel-data` | Type-safe data transfer objects, prevents "array hell" |
| Feature Flags | `laravel/pennant` | Manage rollout of features per tenant/plan |
| Audit Logs | `rmsramos/activitylog` | Track who changed what and when |
| Static Analysis | `larastan/larastan` | Enforces code quality and modular boundaries |

### Core Domains to Build

**Shared Domain** (`Domains/Shared/`)
- Common traits, enums, value objects, DTOs
- Contracts/interfaces for pluggable services (e.g., `ReceiptScanner` interface)
- Shared exceptions and utilities
- The `BelongsToTeam` trait for multi-tenancy

**Auth Domain** (`Domains/Auth/`)
- User model and related logic
- Role definitions and permission checks
- Team/Tenant management

**CRM Domain** (`Domains/CRM/`)
- Customer model (Wholesale vs. Retail, credit limits)
- Supplier model (Feed, Chicks, Meds with performance ratings)
- Contact management

**Finance Domain** (`Domains/Finance/`)
- Expense model with polymorphic allocation (Batch or General Farm)
- OCR service pattern for receipt scanning (pluggable drivers)
- Invoice and Payment models
- Reporting (Aging, P&L, Cost Center Analysis)

**Inventory Domain** (`Domains/Inventory/`)
- Product model (Feed, Medicine, Packaging)
- Warehouse model (multi-location support)
- Stock movement/transaction models with audit trail

**Broiler Domain** (`Domains/Broiler/`) - MVP Focus
- Batch model (aggregate root for a flock cycle)
- Batch status lifecycle (Planned, Active, Harvesting, Closed)
- Daily log entries (Mortality, Feed consumption, Water intake)
- FCR and EPEF calculation logic
- Financial integration with expenses and costs

**IoT Domain** (`Domains/IoT/`) - Future
- Sensor and device models
- Reading/measurement aggregation
- Alert system for anomalies

### Tenancy Strategy (SaaS Foundation)

**Model:** Single database with `team_id` on every critical table.

**Multi-tenancy Filtering:** A `BelongsToTeam` trait must be applied to models to automatically scope queries to the logged-in user's team. This ensures complete data isolation between farms/tenants.

**Team Model:**
- Represents a single farm/business unit
- Has `owner_id` (FK to User), `name`, `subscription_plan` (Basic, Pro, Enterprise)
- All critical models (Expenses, Batches, Products, Customers) have `team_id` foreign key

### RBAC Roles (Filament Shield)

**Four Role Hierarchy:**

- **Super Admin:** System owner. Full access to all data and all tenants. Manages user roles and permissions.
- **Farm Manager:** Full access to financial and operational data for their team. Can create/edit/delete batches, view financials, manage suppliers/customers.
- **Partner:** Read-only access to Financials; Read/Write access to Batches. Limited visibility into costs.
- **Field Worker:** Restricted access. Can only input daily logs (Feed, Mortality, Water). No access to Financials or Admin features.

**Permissions:** Each role is assigned specific permissions via Filament Shield's GUI. Never grant permissions directly to users; always use roles.

### Request Flow

**Frontend (React/Inertia) at `/`:**

1. User navigates to route → Laravel router → Controller
2. Controller returns `Inertia::render('PageName', $data)`
3. Inertia middleware (`HandleInertiaRequests`) injects shared props (auth, app config, etc.)
4. React page component in `resources/js/pages/` receives props and renders
5. Subsequent navigation uses Inertia's SPA-like XHR requests (no full page reload)

**Admin Panel (Filament) at `/admin`:**

1. User visits `/admin` → Filament router → Admin panel
2. If not authenticated, redirects to `/admin/login`
3. Filament renders server-rendered Livewire components
4. Same `web` session guard as React frontend (shared authentication)
5. User actions trigger Livewire updates (real-time, server-side)
6. All queries automatically scoped to user's team via `BelongsToTeam` trait

### Wayfinder Route Pattern
Instead of hardcoding URLs, use type-safe route helpers:

```typescript
// Generated from Laravel routes
import { login, dashboard, profile } from '@/routes';

// Navigate with Inertia
router.visit(dashboard.url);

// Form submission with type-safe routes
profile.update.form().submit({ name, email });

// Access HTTP methods
profile.update.patch(data);  // PATCH request
profile.destroy.delete();    // DELETE request
```

Route definitions are auto-generated in `resources/js/actions/` and `resources/js/routes/` whenever Laravel routes change. The `formVariants: true` option in vite.config.ts enables `.form()` helpers for form submissions with proper method overrides.

### Authentication Architecture

**Filament Admin (`/admin`):**

- **Login/Logout:** Handled by Filament at `/admin/login` and `/admin/logout`
- **User Management:** Create, update, delete users via Filament resources
- **Guard:** Uses Laravel's `web` session guard (same as frontend)
- **Provider:** `app/Providers/Filament/AdminPanelProvider.php`

**Frontend (React) at `/`:**

- **Views Disabled:** Fortify auth view routes disabled (`views => false`)
- **Fortify Service Provider** (`app/Providers/FortifyServiceProvider.php`):
  - Handles password reset backend logic only
  - Registers action classes for password reset
- **User Model:** `app/Models/User` with `TwoFactorAuthenticatable` trait
- **Protected Routes:** Use `auth` middleware in `routes/web.php`

**Shared Authentication:**

- Both Filament and React use the same `web` session guard
- Same `users` database table
- Single login session across both interfaces

### Layout System
Pages use a layered layout approach:

```typescript
// Page component
PageComponent.layout = (page) => (
  <AppLayout>{page}</AppLayout>
);

// AppLayout delegates to AppSidebarLayout which composes:
// AppShell (container) + AppSidebar + AppHeader + content
```

**Layout Variants:**
- `AppLayout` → `AppSidebarLayout` (sidebar navigation)
- `AppLayout` → `AppHeaderLayout` (header-only navigation)
- `AuthLayout` → `AuthSimpleLayout` / `AuthCardLayout` / `AuthSplitLayout`
- `SettingsLayout` (nested within AppLayout with secondary nav)

### Inertia Shared Data
The `HandleInertiaRequests` middleware shares data globally to all pages:

```typescript
interface SharedData {
  auth: { user: User | null };
  appName: string;
  quotes: Array<{ text: string; author: string }>;
  // ... other global state
}
```

Access in components via `usePage<SharedData>().props`.

### Form Handling Pattern
Use Inertia's form helpers for automatic state management:

```typescript
import { router } from '@inertiajs/react';
import { profile } from '@/routes';

// Using Wayfinder + Inertia Form
profile.update.form().submit(
  { name, email },
  {
    onSuccess: () => console.log('Updated!'),
    onError: (errors) => console.log(errors),
  }
);
```

The form component provides `processing`, `recentlySuccessful`, and `errors` state automatically.

## Key Conventions

### File Organization

**React Frontend:**

- **Controllers:** `app/Http/Controllers/` - return Inertia responses with `Inertia::render()`
- **Pages:** `resources/js/pages/` - React page components (auto-resolved by Inertia)
- **Layouts:** `resources/js/layouts/` - layout wrapper components
- **Components:** `resources/js/components/ui/` - shadcn/ui primitives

**Filament Admin:**

- **Panel Provider:** `app/Providers/Filament/AdminPanelProvider.php` - admin panel configuration
- **Resources:** `app/Filament/Resources/` - CRUD resources for models
- **Pages:** `app/Filament/Pages/` - custom admin pages
- **Widgets:** `app/Filament/Widgets/` - dashboard widgets

**Shared:**

- **Models:** `app/Models/` - Eloquent ORM models (shared between React and Filament)
- **Actions:** `app/Actions/Fortify/` - business logic (password reset)
- **Requests:** `app/Http/Requests/` - form validation classes
- **Middleware:** `app/Http/Middleware/` - custom middleware

### TypeScript Types
- `resources/js/types/index.d.ts` - shared types (User, Auth, SharedData)
- Generated route types in `resources/js/actions/` and `resources/js/routes/`

### Styling
- **Tailwind CSS 4** with CSS variables for theming
- Theme management: `useAppearance()` hook in `resources/js/hooks/use-appearance.tsx`
- Light/dark/system modes stored in localStorage and cookie (for SSR)
- Component utilities: `cn()` function from `resources/js/lib/utils.ts` for className merging

### Database
- Default: SQLite (`database/database.sqlite`)
- Migrations in `database/migrations/`
- Queue/cache/sessions use database driver by default

### Queue System
Queue connection: `database` (default)
Process queue jobs:
```bash
php artisan queue:listen --tries=1
```
Queue worker automatically starts with `composer dev`.

## Implementation Roadmap

This project follows a phase-based approach to build features systematically. Each phase builds on the previous, ensuring solid foundations before adding complexity.

### Phase 1: Foundation (Current)

**Focus:** Setup core infrastructure and authentication

- **Auth & Tenancy:** Install `filament-shield`. Define roles (Super Admin, Farm Manager, Partner, Field Worker). Create Team model with multi-tenancy scoping.
- **User Management:** Build User resource in Filament for admin user creation/editing/deletion.
- **CRM (Start):** Build Customer and Supplier resources in Filament with categorization.
- **Finance (Part A):** Expense recording with Interface-based OCR service pattern (pluggable drivers).

**Deliverables:** Filament admin panel with user/customer/supplier management and expense entry.

### Phase 2: Inventory & Advanced Logic

**Focus:** Stock management and financial workflows

- **Inventory:** Product Catalog resource. Stock Movements with audit trail (In/Out).
- **Finance (Part B):** Invoicing (PDF generation), Payments, Aging Reports.
- **DTO Implementation:** Convert all Form Requests to `Spatie\LaravelData` objects for type safety.

**Deliverables:** Complete inventory system and invoicing workflows.

### Phase 3: Broiler Domain (MVP)

**Focus:** Core farm operations for broiler chickens

- **Batch Engine:** Core logic for Batch lifecycle (Planned → Active → Harvesting → Closed).
- **React Frontend:** Build "Field Mode" UI for workers to input daily logs (Mortality, Feed, Water).
- **Analytics:** Real-time calculation of FCR (Feed Conversion Ratio) and EPEF (European Production Efficiency Factor).
- **Financial Link:** Link feed consumed by batches to Finance module as Direct Costs.

**Deliverables:** Complete broiler batch tracking with field operations and analytics.

### Phase 4: Polish & Documentation

**Focus:** Quality assurance and deployment preparation

- **Audit Logs:** Ensure `rmsramos/activitylog` captures all critical events.
- **API Documentation:** Run `dedoc/scramble` to auto-generate OpenAPI/Swagger docs.
- **Code Quality:** Run Larastan for static analysis. Ensure modular boundaries.
- **Deployment:** CI/CD pipeline setup, environment configuration, database optimization.

**Deliverables:** Production-ready codebase with documentation and deployment pipeline.

### Future Phases

- **Phase 5:** Additional agricultural domains (Layers, Hatchery, Livestock).
- **Phase 6:** IoT integration (sensors, automated device management).
- **Phase 7:** SaaS scaling (multi-region, advanced analytics, marketplace).

## Working with Domains (DDD Guidelines)

### Namespace Registration

All Domains are autoloaded via `composer.json`. The namespace is `Domains\\` (backslash escaped in JSON):

```json
"psr-4": {
  "App\\": "app/",
  "Domains\\": "Domains/"
}
```

To use a domain class: `use Domains\Broiler\Models\Batch;`

### Domain Boundaries

**Golden Rule:** Each domain is self-contained. Models, logic, and queries stay within their domain directory.

**Cross-Domain Communication:**
- Use interfaces/contracts (defined in `Domains/Shared/Contracts/`) to avoid tight coupling
- Example: `Domains/Finance/Contracts/Allocatable` interface allows Expenses to link to Batches without importing Broiler logic
- Use events or repositories to decouple operations

**Never:**
- Import from `app/` into a domain (domains are pure business logic)
- Create circular dependencies between domains (Broiler → Finance is OK, but Finance ↛ Broiler)
- Put domain logic in `app/Http/Controllers` (controllers only orchestrate and delegate)

### Model Organization

**App Models** (`app/Models/`):
- User
- Team (required for tenancy)
- Other framework-specific models

**Domain Models** (`Domains/{Domain}/Models/`):
- Domain-specific Eloquent models
- Each domain can have its own models folder

**Example - Broiler Domain:**
```plaintext
Domains/Broiler/
├── Models/
│   ├── Batch.php
│   ├── DailyLog.php
│   └── ...
├── Services/
│   └── FCRCalculator.php
├── Actions/
│   └── CreateBatchAction.php
├── Events/
│   └── BatchCreated.php
└── ...
```

### Creating Domain Classes

Use Laravel artisan with the `--path` flag to generate files in the Domains directory:

```bash
# Create a domain model
php artisan make:model Domains/Broiler/Models/Batch -m

# Create a domain service
php artisan make:class Domains/Finance/Services/InvoiceGenerator

# Create a domain request
php artisan make:request Domains/Broiler/Requests/CreateBatchRequest
```

### Filament Resources

Filament resources live in `app/Filament/Resources/` but they orchestrate domain models:

```php
// app/Filament/Resources/BatchResource.php
use Domains\Broiler\Models\Batch;

class BatchResource extends Resource
{
    protected static ?string $model = Batch::class;
    // ...
}
```

Resources delegate complex logic to domain services/actions, never implementing business logic directly.

### Testing Domain Logic

Place tests alongside the code they test:

```plaintext
tests/
├── Feature/
│   └── Auth/
│       └── LoginTest.php
└── Unit/
    ├── Broiler/
    │   └── FCRCalculatorTest.php
    └── Finance/
        └── InvoiceGeneratorTest.php
```

## Important Notes

### Filament Admin Panel

**Location:** `http://farmsense.test/admin`

**Key Features:**

- Login disabled for public registration (admin-only user creation)
- Two-factor authentication disabled for now (can be enabled in AdminPanelProvider)
- Email verification disabled for now (can be enabled if needed)
- Primary color set to Amber
- Uses Livewire for real-time interactivity
- Components are auto-cached for performance

**Creating Resources:**

Resources are auto-discovered from `app/Filament/Resources/`. To create a resource:
```bash
php artisan make:filament-resource UserResource
```

This generates a full CRUD interface for managing model instances.

**Development Optimizations:**

- Icon caching enabled: `php artisan icons:cache`
- Component caching enabled: `php artisan filament:cache-components`
- OPcache enabled via Laravel Herd
- Debugbar disabled for performance (`DEBUGBAR_ENABLED=false`)
- Xdebug disabled to reduce overhead (`XDEBUG_MODE=off`)

### React Compiler
This project uses the experimental React Compiler (`babel-plugin-react-compiler` in vite.config.ts). This optimizes React rendering automatically, so manual memoization (`useMemo`, `useCallback`) is less critical.

### Wayfinder Regeneration
Route helpers regenerate automatically during dev server (`npm run dev`). If routes seem out of sync, restart the Vite server.

### SSR Considerations
- SSR is optional; app works fine without it
- SSR entry point: `resources/js/ssr.tsx`
- Theme initialization happens in both client and SSR contexts
- When adding new external packages, ensure they're SSR-compatible

### Middleware

**React Frontend:**

- `HandleInertiaRequests`: Shares global data to all Inertia pages
- `HandleAppearance`: Manages theme cookie for SSR

**Filament Admin:**

- `Filament\Http\Middleware\Authenticate`: Protects Filament routes
- `Filament\Http\Middleware\AuthenticateSession`: Validates session authentication
- `DisableBladeIconComponents`: Prevents Blade icon conflicts with Livewire
- `DispatchServingFilamentEvent`: Dispatches Filament serving event

**Shared:**

- `auth`: Protects authenticated routes (both systems)
- `verified`: Requires email verification (currently unused)

### Testing Strategy
- Uses Pest PHP (not PHPUnit syntax)
- Test suites: Unit (`tests/Unit/`) and Feature (`tests/Feature/`)
- Feature tests use in-memory SQLite with array cache/session
- Test environment configured in `phpunit.xml`
