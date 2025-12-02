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

### Tech Stack
- **Backend:** Laravel 12 (PHP 8.2+)
- **Frontend:** React 19 with TypeScript
- **Bridge:** Inertia.js 2.0 (SPA-like experience with server-side routing)
- **Admin Panel:** Filament PHP 4.2 (Livewire-based admin interface)
- **Auth:** Laravel Fortify (password reset backend) + Filament (admin login UI)
- **Build Tool:** Vite 7 with React Compiler (babel-plugin-react-compiler)
- **UI Frontend:** shadcn/ui components (Radix UI primitives + Tailwind CSS 4)
- **UI Admin:** Filament components (built-in UI system)
- **Routing:** Laravel Wayfinder (auto-generates type-safe route helpers from Laravel routes)

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
