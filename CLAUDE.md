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

## Naming Conventions (Strict Rules)

### Directory Structure

**Absolute Rules:**
1. **Top-level Laravel directories:** ALL LOWERCASE
   - `app/`, `config/`, `database/`, `resources/`, `routes/`, `tests/`, `storage/`, `public/`, `bootstrap/`, `vendor/`
   - Custom directories: `docs/`, `plans/`, `reports/`

2. **PascalCase (UpperCamelCase) for subdirectories:**
   - Laravel standard: `Migrations/`, `Seeders/`, `Factories/`, `Controllers/`, `Requests/`, `Models/`, `Providers/`, `Middleware/`
   - Filament: `Resources/`, `Pages/`, `Widgets/`, `Forms/`, `Tables/`
   - Domains: Domain names (e.g., `Auth/`, `CRM/`, `Finance/`, `Shared/`, `Broiler/`, `Inventory/`, `IoT/`)
   - Domain subdirectories: `Models/`, `Services/`, `Actions/`, `Policies/`, `Contracts/`, `Traits/`, `Enums/`, `Drivers/`, `DTOs/`, `Exceptions/`, `Events/`, `tests/` (lowercase `tests` within domains)

**Directory Hierarchy:**
```plaintext
farmsense/
├── app/                           # Lowercase (Laravel standard)
│   ├── Filament/                  # PascalCase (Filament feature)
│   │   ├── Resources/             # PascalCase (Filament feature)
│   │   ├── Pages/                 # PascalCase (Filament feature)
│   │   └── Widgets/               # PascalCase (Filament feature)
│   ├── Http/                      # PascalCase (Laravel standard)
│   │   ├── Controllers/           # PascalCase (Laravel standard)
│   │   ├── Requests/              # PascalCase (Laravel standard)
│   │   └── Middleware/            # PascalCase (Laravel standard)
│   ├── Models/                    # PascalCase (Laravel standard)
│   ├── Providers/                 # PascalCase (Laravel standard)
│   └── Actions/                   # PascalCase (custom, Fortify actions)
│
├── Domains/                       # PascalCase (custom root for DDD)
│   ├── Shared/                    # PascalCase (domain name)
│   │   ├── Traits/                # PascalCase (feature)
│   │   ├── Enums/                 # PascalCase (feature)
│   │   ├── Contracts/             # PascalCase (feature)
│   │   ├── DTOs/                  # PascalCase (feature)
│   │   ├── Exceptions/            # PascalCase (feature)
│   │   └── tests/                 # lowercase (tests subdir)
│   │
│   ├── Auth/                      # PascalCase (domain name)
│   │   ├── Models/                # PascalCase (feature)
│   │   ├── Policies/              # PascalCase (feature)
│   │   ├── Actions/               # PascalCase (feature)
│   │   └── tests/                 # lowercase (tests subdir)
│   │
│   ├── CRM/                       # PascalCase (domain name)
│   ├── Finance/                   # PascalCase (domain name)
│   ├── Inventory/                 # PascalCase (domain name)
│   ├── Broiler/                   # PascalCase (domain name)
│   └── IoT/                       # PascalCase (domain name)
│
├── database/                      # lowercase (Laravel standard)
│   ├── migrations/                # lowercase (Laravel standard)
│   ├── seeders/                   # lowercase (Laravel standard)
│   └── factories/                 # lowercase (Laravel standard)
│
├── resources/                     # lowercase (Laravel standard)
├── routes/                        # lowercase (Laravel standard)
├── tests/                         # lowercase (Laravel standard)
├── config/                        # lowercase (Laravel standard)
├── storage/                       # lowercase (Laravel standard)
├── public/                        # lowercase (Laravel standard)
├── docs/                          # lowercase (custom)
├── plans/                         # lowercase (custom)
└── reports/                       # lowercase (custom)
```

### File Naming

1. **Class Files:** PascalCase (matches class name)
   - `User.php` (class User)
   - `CreateUserAction.php` (class CreateUserAction)
   - `UserPolicy.php` (class UserPolicy)
   - `UserRepository.php` (class UserRepository)
   - `BelongsToTeam.php` (trait BelongsToTeam)
   - `CustomerType.php` (enum CustomerType)

2. **Configuration Files:** lowercase with hyphens
   - `config/app.php`
   - `config/database.php`
   - `config/filament-shield.php`

3. **Migration Files:** timestamp + snake_case + action
   - `2025_12_02_201315_create_teams_table.php`
   - `2025_12_02_201316_create_team_user_table.php`
   - `2025_12_02_201317_add_team_context_to_users_table.php`

4. **Seeder Files:** PascalCase
   - `DatabaseSeeder.php`
   - `RoleAndPermissionSeeder.php`
   - `SupplierSeeder.php`

5. **Test Files:** PascalCase + "Test" suffix
   - `TeamManagementTest.php`
   - `AuthorizationTest.php`
   - `SupplierTest.php`
   - `ExpensePolicyTest.php`

6. **Route Files:** lowercase
   - `routes/web.php`
   - `routes/api.php`

7. **Frontend Files:**
   - **React Components:** PascalCase
     - `components/Button.tsx`
     - `components/ui/Card.tsx`
     - `pages/Dashboard.tsx`
     - `layouts/AppLayout.tsx`

   - **Hooks:** lowercase with `use` prefix
     - `hooks/use-appearance.tsx`
     - `hooks/use-auth.ts`

   - **Utilities:** lowercase with hyphens
     - `lib/utils.ts`
     - `lib/api-client.ts`

### Class & Function Naming

1. **Classes:** PascalCase
   - `class User`
   - `class CreateUserAction`
   - `class UserPolicy`
   - `trait BelongsToTeam`
   - `enum CustomerType`
   - `interface Allocatable`

2. **Methods & Functions:** camelCase
   - `public function getCurrentTeamId()`
   - `public function setCurrentTeam()`
   - `public function scopeBelongsToTeam()`
   - `private function calculateFCR()`

3. **Constants:** SCREAMING_SNAKE_CASE
   - `const DEFAULT_PAGINATION = 15;`
   - `const CACHE_TTL_HOURS = 24;`

4. **Variables & Properties:** camelCase
   - `protected $currentTeamId;`
   - `private $cache;`
   - `$userId = 123;`

### Database Naming

1. **Table Names:** snake_case, plural
   - `users`
   - `teams`
   - `team_user` (pivot)
   - `customers`
   - `suppliers`
   - `expenses`

2. **Column Names:** snake_case
   - `user_id` (foreign key)
   - `team_id` (foreign key)
   - `role_id` (foreign key)
   - `current_team_id` (relationship reference)
   - `email_verified_at` (timestamp)
   - `created_at` (timestamp)
   - `updated_at` (timestamp)

3. **Foreign Key Naming:** `{table_singular}_id`
   - `user_id` (references users table)
   - `team_id` (references teams table)
   - `supplier_id` (references suppliers table)

4. **Pivot Table Naming:** `{model1_singular}_{model2_singular}` (alphabetical if same length)
   - `team_user` (not `user_team`)
   - `role_team` (not `team_role`)

### PHP Namespace Naming

1. **App Namespace:**
   - `App\Models\User`
   - `App\Http\Controllers\DashboardController`
   - `App\Filament\Resources\UserResource`
   - `App\Providers\AuthServiceProvider`

2. **Domain Namespace:**
   - `Domains\Auth\Models\User`
   - `Domains\Auth\Policies\UserPolicy`
   - `Domains\CRM\Models\Supplier`
   - `Domains\Finance\Models\Expense`
   - `Domains\Shared\Traits\BelongsToTeam`
   - `Domains\Shared\Enums\CustomerType`

3. **Test Namespace:** Follow domain structure
   - `Domains\Auth\Tests\Feature\TeamManagementTest`
   - `Domains\CRM\Tests\Feature\SupplierTest`
   - `Domains\Finance\Tests\Feature\ExpensePolicyTest`

### Filament Resource Naming

1. **Resource Classes:** PascalCase matching model name + "Resource"
   - `UserResource.php`
   - `TeamResource.php`
   - `SupplierResource.php`
   - `ExpenseResource.php`

2. **Resource Namespace:** `App\Filament\Resources\`

3. **Resource Pages:** Nested in resource subdirectories
   - `app/Filament/Resources/UserResource/Pages/ListUsers.php`
   - `app/Filament/Resources/UserResource/Pages/CreateUser.php`
   - `app/Filament/Resources/UserResource/Pages/EditUser.php`

### Naming Convention Rules (Enforcement)

- **Never deviate:** These rules are non-negotiable for consistency
- **Case-sensitive:** Linux servers are case-sensitive; maintain exact casing everywhere
- **Windows vs Linux:** Windows ignores case, Linux enforces it. Always write code as if on Linux
- **Automated:** Use Laravel Pint for PHP code formatting: `./vendor/bin/pint`
- **IDE Help:** Configure your IDE to respect these conventions (ESLint, Prettier, Laravel Pint)

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

## Implementation Reporting

To maintain focused development and avoid documentation overhead:

**Reporting Rule:** Complete one comprehensive implementation report **per plan completion**, not per step.

**Workflow:**

1. Review and approve plan (e.g., `plans/PHASE_1.md`)
2. Implement all steps in the plan
3. At the end, create a single report (e.g., `reports/PHASE_1_IMPLEMENTATION_REPORT.md`)
4. Use the `reports/IMPLEMENTATION_REPORT_TEMPLATE.md` as your guide

**Report Requirements:**

- ✅ Executive summary of what was built
- ✅ Features implemented with acceptance criteria
- ✅ Architecture changes with Mermaid diagrams
- ✅ Database schema and ER diagrams
- ✅ All git commits made during the phase
- ✅ Test coverage and quality metrics
- ✅ Deployment notes
- ✅ Lessons learned
- ✅ Cross-domain impact analysis

**Why This Approach:**

- Keeps documentation manageable and sprintable
- Avoids fragmentation across multiple micro-reports
- Provides complete context for future reference
- Faster to execute and easier to maintain

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.15
- filament/filament (FILAMENT) - v4
- inertiajs/inertia-laravel (INERTIA) - v2
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/wayfinder (WAYFINDER) - v0
- livewire/livewire (LIVEWIRE) - v3
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- @inertiajs/react (INERTIA) - v2
- react (REACT) - v19
- tailwindcss (TAILWINDCSS) - v4
- @laravel/vite-plugin-wayfinder (WAYFINDER) - v0
- eslint (ESLINT) - v9
- prettier (PRETTIER) - v3

## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure - don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.


=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs
- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms


=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.


=== herd rules ===

## Laravel Herd

- The application is served by Laravel Herd and will be available at: https?://[kebab-case-project-dir].test. Use the `get-absolute-url` tool to generate URLs for the user to ensure valid URLs.
- You must not run any commands to make the site available via HTTP(s). It is _always_ available through Laravel Herd.


=== inertia-laravel/core rules ===

## Inertia Core

- Inertia.js components should be placed in the `resources/js/Pages` directory unless specified differently in the JS bundler (vite.config.js).
- Use `Inertia::render()` for server-side routing instead of traditional Blade views.
- Use `search-docs` for accurate guidance on all things Inertia.

<code-snippet lang="php" name="Inertia::render Example">
// routes/web.php example
Route::get('/users', function () {
    return Inertia::render('Users/Index', [
        'users' => User::all()
    ]);
});
</code-snippet>


=== inertia-laravel/v2 rules ===

## Inertia v2

- Make use of all Inertia features from v1 & v2. Check the documentation before making any changes to ensure we are taking the correct approach.

### Inertia v2 New Features
- Polling
- Prefetching
- Deferred props
- Infinite scrolling using merging props and `WhenVisible`
- Lazy loading data on scroll

### Deferred Props & Empty States
- When using deferred props on the frontend, you should add a nice empty state with pulsing / animated skeleton.

### Inertia Form General Guidance
- The recommended way to build forms when using Inertia is with the `<Form>` component - a useful example is below. Use `search-docs` with a query of `form component` for guidance.
- Forms can also be built using the `useForm` helper for more programmatic control, or to follow existing conventions. Use `search-docs` with a query of `useForm helper` for guidance.
- `resetOnError`, `resetOnSuccess`, and `setDefaultsOnSuccess` are available on the `<Form>` component. Use `search-docs` with a query of 'form component resetting' for guidance.


=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.


=== laravel/v12 rules ===

## Laravel 12

- Use the `search-docs` tool to get version specific documentation.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

### Laravel 12 Structure
- No middleware files in `app/Http/Middleware/`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- **No app\Console\Kernel.php** - use `bootstrap/app.php` or `routes/console.php` for console configuration.
- **Commands auto-register** - files in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database
- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models
- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.


=== wayfinder/core rules ===

## Laravel Wayfinder

Wayfinder generates TypeScript functions and types for Laravel controllers and routes which you can import into your client side code. It provides type safety and automatic synchronization between backend routes and frontend code.

### Development Guidelines
- Always use `search-docs` to check wayfinder correct usage before implementing any features.
- Always Prefer named imports for tree-shaking (e.g., `import { show } from '@/actions/...'`)
- Avoid default controller imports (prevents tree-shaking)
- Run `php artisan wayfinder:generate` after route changes if Vite plugin isn't installed

### Feature Overview
- Form Support: Use `.form()` with `--with-form` flag for HTML form attributes — `<form {...store.form()}>` → `action="/posts" method="post"`
- HTTP Methods: Call `.get()`, `.post()`, `.patch()`, `.put()`, `.delete()` for specific methods — `show.head(1)` → `{ url: "/posts/1", method: "head" }`
- Invokable Controllers: Import and invoke directly as functions. For example, `import StorePost from '@/actions/.../StorePostController'; StorePost()`
- Named Routes: Import from `@/routes/` for non-controller routes. For example, `import { show } from '@/routes/post'; show(1)` for route name `post.show`
- Parameter Binding: Detects route keys (e.g., `{post:slug}`) and accepts matching object properties — `show("my-post")` or `show({ slug: "my-post" })`
- Query Merging: Use `mergeQuery` to merge with `window.location.search`, set values to `null` to remove — `show(1, { mergeQuery: { page: 2, sort: null } })`
- Query Parameters: Pass `{ query: {...} }` in options to append params — `show(1, { query: { page: 1 } })` → `"/posts/1?page=1"`
- Route Objects: Functions return `{ url, method }` shaped objects — `show(1)` → `{ url: "/posts/1", method: "get" }`
- URL Extraction: Use `.url()` to get URL string — `show.url(1)` → `"/posts/1"`

### Example Usage

<code-snippet name="Wayfinder Basic Usage" lang="typescript">
    // Import controller methods (tree-shakable)
    import { show, store, update } from '@/actions/App/Http/Controllers/PostController'

    // Get route object with URL and method...
    show(1) // { url: "/posts/1", method: "get" }

    // Get just the URL...
    show.url(1) // "/posts/1"

    // Use specific HTTP methods...
    show.get(1) // { url: "/posts/1", method: "get" }
    show.head(1) // { url: "/posts/1", method: "head" }

    // Import named routes...
    import { show as postShow } from '@/routes/post' // For route name 'post.show'
    postShow(1) // { url: "/posts/1", method: "get" }
</code-snippet>


### Wayfinder + Inertia
If your application uses the `<Form>` component from Inertia, you can use Wayfinder to generate form action and method automatically.
<code-snippet name="Wayfinder Form Component (React)" lang="typescript">

<Form {...store.form()}><input name="title" /></Form>

</code-snippet>


=== livewire/core rules ===

## Livewire Core
- Use the `search-docs` tool to find exact version specific documentation for how to write Livewire & Livewire tests.
- Use the `php artisan make:livewire [Posts\CreatePost]` artisan command to create new components
- State should live on the server, with the UI reflecting it.
- All Livewire requests hit the Laravel backend, they're like regular HTTP requests. Always validate form data, and run authorization checks in Livewire actions.

## Livewire Best Practices
- Livewire components require a single root element.
- Use `wire:loading` and `wire:dirty` for delightful loading states.
- Add `wire:key` in loops:

    ```blade
    @foreach ($items as $item)
        <div wire:key="item-{{ $item->id }}">
            {{ $item->name }}
        </div>
    @endforeach
    ```

- Prefer lifecycle hooks like `mount()`, `updatedFoo()` for initialization and reactive side effects:

<code-snippet name="Lifecycle hook examples" lang="php">
    public function mount(User $user) { $this->user = $user; }
    public function updatedSearch() { $this->resetPage(); }
</code-snippet>


## Testing Livewire

<code-snippet name="Example Livewire component test" lang="php">
    Livewire::test(Counter::class)
        ->assertSet('count', 0)
        ->call('increment')
        ->assertSet('count', 1)
        ->assertSee(1)
        ->assertStatus(200);
</code-snippet>


    <code-snippet name="Testing a Livewire component exists within a page" lang="php">
        $this->get('/posts/create')
        ->assertSeeLivewire(CreatePost::class);
    </code-snippet>


=== livewire/v3 rules ===

## Livewire 3

### Key Changes From Livewire 2
- These things changed in Livewire 2, but may not have been updated in this application. Verify this application's setup to ensure you conform with application conventions.
    - Use `wire:model.live` for real-time updates, `wire:model` is now deferred by default.
    - Components now use the `App\Livewire` namespace (not `App\Http\Livewire`).
    - Use `$this->dispatch()` to dispatch events (not `emit` or `dispatchBrowserEvent`).
    - Use the `components.layouts.app` view as the typical layout path (not `layouts.app`).

### New Directives
- `wire:show`, `wire:transition`, `wire:cloak`, `wire:offline`, `wire:target` are available for use. Use the documentation to find usage examples.

### Alpine
- Alpine is now included with Livewire, don't manually include Alpine.js.
- Plugins included with Alpine: persist, intersect, collapse, and focus.

### Lifecycle Hooks
- You can listen for `livewire:init` to hook into Livewire initialization, and `fail.status === 419` for the page expiring:

<code-snippet name="livewire:load example" lang="js">
document.addEventListener('livewire:init', function () {
    Livewire.hook('request', ({ fail }) => {
        if (fail && fail.status === 419) {
            alert('Your session expired');
        }
    });

    Livewire.hook('message.failed', (message, component) => {
        console.error(message);
    });
});
</code-snippet>


=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.


=== pest/core rules ===

## Pest
### Testing
- If you need to verify a feature is working, write or update a Unit / Feature test.

### Pest Tests
- All tests must be written using Pest. Use `php artisan make:test --pest {name}`.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files - these are core to the application.
- Tests should test all of the happy paths, failure paths, and weird paths.
- Tests live in the `tests/Feature` and `tests/Unit` directories.
- Pest tests look and behave like this:
<code-snippet name="Basic Pest Test Example" lang="php">
it('is true', function () {
    expect(true)->toBeTrue();
});
</code-snippet>

### Running Tests
- Run the minimal number of tests using an appropriate filter before finalizing code edits.
- To run all tests: `php artisan test`.
- To run all tests in a file: `php artisan test tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --filter=testName` (recommended after making a change to a related file).
- When the tests relating to your changes are passing, ask the user if they would like to run the entire test suite to ensure everything is still passing.

### Pest Assertions
- When asserting status codes on a response, use the specific method like `assertForbidden` and `assertNotFound` instead of using `assertStatus(403)` or similar, e.g.:
<code-snippet name="Pest Example Asserting postJson Response" lang="php">
it('returns all', function () {
    $response = $this->postJson('/api/docs', []);

    $response->assertSuccessful();
});
</code-snippet>

### Mocking
- Mocking can be very helpful when appropriate.
- When mocking, you can use the `Pest\Laravel\mock` Pest function, but always import it via `use function Pest\Laravel\mock;` before using it. Alternatively, you can use `$this->mock()` if existing tests do.
- You can also create partial mocks using the same import or self method.

### Datasets
- Use datasets in Pest to simplify tests which have a lot of duplicated data. This is often the case when testing validation rules, so consider going with this solution when writing tests for validation rules.

<code-snippet name="Pest Dataset Example" lang="php">
it('has emails', function (string $email) {
    expect($email)->not->toBeEmpty();
})->with([
    'james' => 'james@laravel.com',
    'taylor' => 'taylor@laravel.com',
]);
</code-snippet>


=== pest/v4 rules ===

## Pest 4

- Pest v4 is a huge upgrade to Pest and offers: browser testing, smoke testing, visual regression testing, test sharding, and faster type coverage.
- Browser testing is incredibly powerful and useful for this project.
- Browser tests should live in `tests/Browser/`.
- Use the `search-docs` tool for detailed guidance on utilizing these features.

### Browser Testing
- You can use Laravel features like `Event::fake()`, `assertAuthenticated()`, and model factories within Pest v4 browser tests, as well as `RefreshDatabase` (when needed) to ensure a clean state for each test.
- Interact with the page (click, type, scroll, select, submit, drag-and-drop, touch gestures, etc.) when appropriate to complete the test.
- If requested, test on multiple browsers (Chrome, Firefox, Safari).
- If requested, test on different devices and viewports (like iPhone 14 Pro, tablets, or custom breakpoints).
- Switch color schemes (light/dark mode) when appropriate.
- Take screenshots or pause tests for debugging when appropriate.

### Example Tests

<code-snippet name="Pest Browser Test Example" lang="php">
it('may reset the password', function () {
    Notification::fake();

    $this->actingAs(User::factory()->create());

    $page = visit('/sign-in'); // Visit on a real browser...

    $page->assertSee('Sign In')
        ->assertNoJavascriptErrors() // or ->assertNoConsoleLogs()
        ->click('Forgot Password?')
        ->fill('email', 'nuno@laravel.com')
        ->click('Send Reset Link')
        ->assertSee('We have emailed your password reset link!')

    Notification::assertSent(ResetPassword::class);
});
</code-snippet>

<code-snippet name="Pest Smoke Testing Example" lang="php">
$pages = visit(['/', '/about', '/contact']);

$pages->assertNoJavascriptErrors()->assertNoConsoleLogs();
</code-snippet>


=== inertia-react/core rules ===

## Inertia + React

- Use `router.visit()` or `<Link>` for navigation instead of traditional links.

<code-snippet name="Inertia Client Navigation" lang="react">

import { Link } from '@inertiajs/react'
<Link href="/">Home</Link>

</code-snippet>


=== inertia-react/v2/forms rules ===

## Inertia + React Forms

<code-snippet name="`<Form>` Component Example" lang="react">

import { Form } from '@inertiajs/react'

export default () => (
    <Form action="/users" method="post">
        {({
            errors,
            hasErrors,
            processing,
            wasSuccessful,
            recentlySuccessful,
            clearErrors,
            resetAndClearErrors,
            defaults
        }) => (
        <>
        <input type="text" name="name" />

        {errors.name && <div>{errors.name}</div>}

        <button type="submit" disabled={processing}>
            {processing ? 'Creating...' : 'Create User'}
        </button>

        {wasSuccessful && <div>User created successfully!</div>}
        </>
    )}
    </Form>
)

</code-snippet>


=== tailwindcss/core rules ===

## Tailwind Core

- Use Tailwind CSS classes to style HTML, check and use existing tailwind conventions within the project before writing your own.
- Offer to extract repeated patterns into components that match the project's conventions (i.e. Blade, JSX, Vue, etc..)
- Think through class placement, order, priority, and defaults - remove redundant classes, add classes to parent or child carefully to limit repetition, group elements logically
- You can use the `search-docs` tool to get exact examples from the official documentation when needed.

### Spacing
- When listing items, use gap utilities for spacing, don't use margins.

    <code-snippet name="Valid Flex Gap Spacing Example" lang="html">
        <div class="flex gap-8">
            <div>Superior</div>
            <div>Michigan</div>
            <div>Erie</div>
        </div>
    </code-snippet>


### Dark Mode
- If existing pages and components support dark mode, new pages and components must support dark mode in a similar way, typically using `dark:`.


=== tailwindcss/v4 rules ===

## Tailwind 4

- Always use Tailwind CSS v4 - do not use the deprecated utilities.
- `corePlugins` is not supported in Tailwind v4.
- In Tailwind v4, configuration is CSS-first using the `@theme` directive — no separate `tailwind.config.js` file is needed.
<code-snippet name="Extending Theme in CSS" lang="css">
@theme {
  --color-brand: oklch(0.72 0.11 178);
}
</code-snippet>

- In Tailwind v4, you import Tailwind using a regular CSS `@import` statement, not using the `@tailwind` directives used in v3:

<code-snippet name="Tailwind v4 Import Tailwind Diff" lang="diff">
   - @tailwind base;
   - @tailwind components;
   - @tailwind utilities;
   + @import "tailwindcss";
</code-snippet>


### Replaced Utilities
- Tailwind v4 removed deprecated utilities. Do not use the deprecated option - use the replacement.
- Opacity values are still numeric.

| Deprecated |	Replacement |
|------------+--------------|
| bg-opacity-* | bg-black/* |
| text-opacity-* | text-black/* |
| border-opacity-* | border-black/* |
| divide-opacity-* | divide-black/* |
| ring-opacity-* | ring-black/* |
| placeholder-opacity-* | placeholder-black/* |
| flex-shrink-* | shrink-* |
| flex-grow-* | grow-* |
| overflow-ellipsis | text-ellipsis |
| decoration-slice | box-decoration-slice |
| decoration-clone | box-decoration-clone |


=== filament/filament rules ===

## Filament
- Filament is used by this application, check how and where to follow existing application conventions.
- Filament is a Server-Driven UI (SDUI) framework for Laravel. It allows developers to define user interfaces in PHP using structured configuration objects. It is built on top of Livewire, Alpine.js, and Tailwind CSS.
- You can use the `search-docs` tool to get information from the official Filament documentation when needed. This is very useful for Artisan command arguments, specific code examples, testing functionality, relationship management, and ensuring you're following idiomatic practices.
- Utilize static `make()` methods for consistent component initialization.

### Artisan
- You must use the Filament specific Artisan commands to create new files or components for Filament. You can find these with the `list-artisan-commands` tool, or with `php artisan` and the `--help` option.
- Inspect the required options, always pass `--no-interaction`, and valid arguments for other options when applicable.

### Filament's Core Features
- Actions: Handle doing something within the application, often with a button or link. Actions encapsulate the UI, the interactive modal window, and the logic that should be executed when the modal window is submitted. They can be used anywhere in the UI and are commonly used to perform one-time actions like deleting a record, sending an email, or updating data in the database based on modal form input.
- Forms: Dynamic forms rendered within other features, such as resources, action modals, table filters, and more.
- Infolists: Read-only lists of data.
- Notifications: Flash notifications displayed to users within the application.
- Panels: The top-level container in Filament that can include all other features like pages, resources, forms, tables, notifications, actions, infolists, and widgets.
- Resources: Static classes that are used to build CRUD interfaces for Eloquent models. Typically live in `app/Filament/Resources`.
- Schemas: Represent components that define the structure and behavior of the UI, such as forms, tables, or lists.
- Tables: Interactive tables with filtering, sorting, pagination, and more.
- Widgets: Small component included within dashboards, often used for displaying data in charts, tables, or as a stat.

### Relationships
- Determine if you can use the `relationship()` method on form components when you need `options` for a select, checkbox, repeater, or when building a `Fieldset`:

<code-snippet name="Relationship example for Form Select" lang="php">
Forms\Components\Select::make('user_id')
    ->label('Author')
    ->relationship('author')
    ->required(),
</code-snippet>


## Testing
- It's important to test Filament functionality for user satisfaction.
- Ensure that you are authenticated to access the application within the test.
- Filament uses Livewire, so start assertions with `livewire()` or `Livewire::test()`.

### Example Tests

<code-snippet name="Filament Table Test" lang="php">
    livewire(ListUsers::class)
        ->assertCanSeeTableRecords($users)
        ->searchTable($users->first()->name)
        ->assertCanSeeTableRecords($users->take(1))
        ->assertCanNotSeeTableRecords($users->skip(1))
        ->searchTable($users->last()->email)
        ->assertCanSeeTableRecords($users->take(-1))
        ->assertCanNotSeeTableRecords($users->take($users->count() - 1));
</code-snippet>

<code-snippet name="Filament Create Resource Test" lang="php">
    livewire(CreateUser::class)
        ->fillForm([
            'name' => 'Howdy',
            'email' => 'howdy@example.com',
        ])
        ->call('create')
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseHas(User::class, [
        'name' => 'Howdy',
        'email' => 'howdy@example.com',
    ]);
</code-snippet>

<code-snippet name="Testing Multiple Panels (setup())" lang="php">
    use Filament\Facades\Filament;

    Filament::setCurrentPanel('app');
</code-snippet>

<code-snippet name="Calling an Action in a Test" lang="php">
    livewire(EditInvoice::class, [
        'invoice' => $invoice,
    ])->callAction('send');

    expect($invoice->refresh())->isSent()->toBeTrue();
</code-snippet>


### Important Version 4 Changes
- File visibility is now `private` by default.
- The `deferFilters` method from Filament v3 is now the default behavior in Filament v4, so users must click a button before the filters are applied to the table. To disable this behavior, you can use the `deferFilters(false)` method.
- The `Grid`, `Section`, and `Fieldset` layout components no longer span all columns by default.
- The `all` pagination page method is not available for tables by default.
- All action classes extend `Filament\Actions\Action`. No action classes exist in `Filament\Tables\Actions`.
- The `Form` & `Infolist` layout components have been moved to `Filament\Schemas\Components`, for example `Grid`, `Section`, `Fieldset`, `Tabs`, `Wizard`, etc.
- A new `Repeater` component for Forms has been added.
- Icons now use the `Filament\Support\Icons\Heroicon` Enum by default. Other options are available and documented.

### Organize Component Classes Structure
- Schema components: `Schemas/Components/`
- Table columns: `Tables/Columns/`
- Table filters: `Tables/Filters/`
- Actions: `Actions/`
</laravel-boost-guidelines>
