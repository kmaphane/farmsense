# Migration Plan: Inertia Auth → Filament Admin

## Current State
- **Login/Register/Auth:** Fortify + Inertia.js (React pages at `/login`, `/register`, `/dashboard`)
- **Auth Guard:** `web` (session-based)
- **Dashboard:** React component at `/dashboard` with Inertia
- **Settings:** Profile, password, 2FA in React pages
- **Filament:** Recently installed, admin panel at `/admin` with its own login

## Goal
Consolidate all admin/internal functionality to Filament while keeping public-facing React frontend separate.

## Architecture After Migration
```
Public Facing (React/Inertia)
├── `/` → Welcome page (public, with register/login links)
└── (Optional: User-facing features via React if needed)

Admin/Internal (Filament)
├── `/admin` → Filament admin dashboard (authenticated)
├── `/admin/login` → Filament login (uses shared User model)
└── `/admin/logout` → Filament logout
```

## Implementation Strategy

### Phase 1: Keep Both Systems Running in Parallel (Safe Transition)
This approach ensures no functionality loss while we transition:

**1.1 Disable Fortify's Custom Auth Views**
- Set `Fortify::views => false` in FortifyServiceProvider
- This disables Fortify's auth routes (`/login`, `/register`, etc.)
- Keep Fortify's backend logic for now (validation, actions)
- Reason: Filament will handle all auth, eliminating route conflicts

**1.2 Update Fortify Config**
- Set `home` redirect to `/admin` instead of `/dashboard`
- Rationale: After login, direct users to Filament admin instead of React dashboard

**1.3 Update Web Routes**
- Remove `/dashboard` route (now at `/admin` in Filament)
- Keep `/` welcome page (for public access)
- Remove settings routes (move to Filament if needed)

**1.4 Verify Authentication Sharing**
- Both Filament and React use the same `web` guard
- Both use the same `users` table
- No new user authentication logic needed

### Phase 2: Clean Up Inertia Auth Components
After transitioning all users to Filament login:

**2.1 Remove Auth Pages**
- Delete `/resources/js/pages/auth/` (all React auth pages)
  - `login.tsx`
  - `register.tsx`
  - `forgot-password.tsx`
  - `reset-password.tsx`
  - `verify-email.tsx`
  - `confirm-password.tsx`
  - `two-factor-challenge.tsx`

**2.2 Remove Settings Routes**
- Delete `/routes/settings.php`
- Delete settings pages from React

**2.3 Remove FortifyServiceProvider Logic**
- Keep the service provider file but remove view configurations
- Or delete entirely if Fortify customization no longer needed

**2.4 Optionally Remove Fortify Dependency**
- If no React auth needed, consider removing `laravel/fortify`
- Only do this AFTER confirming Filament handles all auth needs

### Phase 3: Filament-Specific Setup
Create admin resources and customize the Filament admin panel:

**3.1 Create Filament Resources** (as needed)
- User management resource (CRUD for users)
- Any domain-specific resources

**3.2 Customize AdminPanelProvider**
- Theme colors (already set to Amber)
- Navigation items
- Sidebar organization
- Custom pages or widgets

**3.3 Set Up Email Verification** (if needed)
- Filament supports email verification
- May need custom page if required

**3.4 Two-Factor Auth** (if needed)
- Filament has built-in 2FA support
- Can reuse existing 2FA logic or use Filament's implementation

### Phase 4: Testing & Validation
**4.1 Test Login Flow**
- Verify users can log in via `/admin/login`
- Verify proper redirect to `/admin` after login
- Verify logout works

**4.2 Test Route Protection**
- Verify unauthenticated users can't access `/admin`
- Verify public routes still accessible

**4.3 Test User Data Consistency**
- Verify same user records work in both systems
- Verify session persistence

## Key Configuration Changes

### config/fortify.php
```php
'views' => false,  // Disable Fortify's view routes
'home' => '/admin', // Redirect to Filament after login
```

### routes/web.php
```php
// Remove this:
// Route::get('dashboard', ...)->name('dashboard');
// Route::middleware('auth')->group(function () { ... }); // settings

// Keep this:
Route::get('/', function () { ... })->name('home'); // Public welcome
```

### FortifyServiceProvider
```php
// Remove or comment out:
// Fortify::loginView(...)
// Fortify::registerView(...)
// All other view configurations
```

## Risks & Mitigation

### Risk 1: Users Locked Out
- **Impact:** Users can't log in during transition
- **Mitigation:** Both systems share same User model and session guard; Filament auth will work immediately

### Risk 2: Route Conflicts
- **Impact:** Fortify routes conflict with Filament
- **Mitigation:** Disable Fortify views (`views => false`) to prevent route registration

### Risk 3: Session/Guard Incompatibility
- **Impact:** Users logged in via Filament can't access React frontend
- **Mitigation:** Both use `web` guard; no incompatibility

### Risk 4: Lost Functionality
- **Impact:** Settings pages, 2FA no longer accessible
- **Mitigation:** Recreate critical features in Filament as needed

## Rollback Plan
If needed to revert:
1. Set `Fortify::views => true`
2. Restore routes/web.php
3. Restore FortifyServiceProvider auth views
4. Set `home` back to `/dashboard`

## Files to Modify
1. `/config/fortify.php` - Disable views, update home redirect
2. `/routes/web.php` - Remove dashboard and settings routes
3. `/app/Providers/FortifyServiceProvider.php` - Remove/disable view configurations
4. `/app/Providers/Filament/AdminPanelProvider.php` - Customize as needed

## Files to Delete (Optional)
1. `/resources/js/pages/auth/` - All auth pages
2. `/resources/js/pages/settings/` - Settings pages
3. `/routes/settings.php` - Settings routes
4. `/app/Providers/FortifyServiceProvider.php` - If Fortify completely removed

## Timeline (Logical Order)
1. Disable Fortify auth views
2. Update redirect paths
3. Remove web routes
4. Test authentication flow
5. Clean up React pages (when ready)
6. Create Filament resources as needed
