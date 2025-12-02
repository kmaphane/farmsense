# Phase 1 Implementation Plan: Foundation

**Status:** Ready for Review
**Phase:** 1 of 4
**Focus:** Setup core infrastructure and authentication with multi-tenancy

---

## Overview

Phase 1 establishes the foundation for Farmsense by implementing:
1. Multi-tenancy support (Teams)
2. Domain-Driven Design structure
3. Role-Based Access Control (Filament Shield)
4. User management in Filament admin
5. Basic CRM and Finance domains
6. Core testing infrastructure

**Expected Outcome:** A functional Filament admin panel with user/team/customer/supplier management and expense entry capability.

---

## Architecture Decisions

### 1. Multi-Tenancy Approach: Single Database with Many-to-Many Teams

**Decision Rationale:**
- Single database is simpler than database-per-tenant
- Better for SaaS scaling to small-medium farms
- Users can belong to multiple teams (farms) with different roles per team
- Easier data isolation with Eloquent scoping and team context
- Cost-effective for initial deployment to Botswana
- Allows farmers to collaborate across multiple operations

**Implementation:**
- Add `teams` table (organizations/farms)
- Create `team_user` pivot table with user_id, team_id, and role_id
- User has many teams through pivot (many-to-many)
- Each team_user record stores the user's role within that team
- Add `team_id` to all shared tables (expenses, customers, suppliers, etc.)
- Use `BelongsToTeam` trait on all scoped models for automatic filtering by current team context
- Use middleware to set "current team" context per request

### 2. Domain-Driven Design Structure

**Folder Organization:**
```
Domains/
├── Shared/
│   ├── Traits/           (BelongsToTeam, HasTeam, etc.)
│   ├── Enums/            (Status, Categories, etc.)
│   ├── Contracts/        (Allocatable, ReceiptScanner, etc.)
│   ├── DTOs/             (Data Transfer Objects)
│   └── Exceptions/       (Custom exceptions)
├── Auth/
│   ├── Models/           (User, Team)
│   ├── Actions/          (CreateUser, etc.)
│   └── Policies/         (Authorization)
├── CRM/
│   ├── Models/           (Customer, Supplier)
│   ├── Services/         (Customer management)
│   └── Enums/            (CustomerType, SupplierCategory)
├── Finance/
│   ├── Models/           (Expense, Invoice, Payment)
│   ├── Services/         (Invoicing, etc.)
│   ├── Contracts/        (ReceiptScanner interface)
│   └── Drivers/          (OCR implementations)
└── Inventory/
    ├── Models/           (Product, Stock, Movement)
    └── Services/         (Stock management)
```

### 3. RBAC Role Hierarchy

**Four-Role Structure:**

| Role | Access Level | Use Case |
|------|---|---|
| **Super Admin** | All data, all teams, manage roles | System owner/Developer |
| **Farm Manager** | Full team access (financial + operational) | Farm owner/Manager |
| **Partner** | Read-only financials, read/write batches | Cooperative partner |
| **Field Worker** | Daily logs only (mortality, feed) | Farm employee |

**Implementation:**
- Use Filament Shield to define permissions
- Create role seeders to populate permissions
- Apply policies to Filament Resources

---

## Step-by-Step Implementation

### STEP 1: Create Domain Structure & Base Classes (Est. 30 min)

**Tasks:**
1. Create `Domains/` folder structure (all subdirectories)
2. Create `Domains/Shared/Traits/BelongsToTeam.php`
   - Automatically scope queries by `team_id`
   - Use local scopes: `belongsToTeam($team = null)`
3. Create `Domains/Shared/Enums/` base enums (Status, Categories)
4. Create `Domains/Shared/Contracts/` interfaces
5. Update `composer.json` to autoload `Domains\\` namespace
6. Run `composer dump-autoload`

**Files to Create:**
- `Domains/Shared/Traits/BelongsToTeam.php`
- `Domains/Shared/Enums/CustomerType.php`
- `Domains/Shared/Enums/SupplierCategory.php`
- `Domains/Shared/Enums/ExpenseCategory.php`
- `Domains/Shared/Contracts/Allocatable.php` (for polymorphic expenses)
- `Domains/Shared/Contracts/ReceiptScanner.php` (for OCR interface)

**Tests:**
- Unit tests for `BelongsToTeam` trait scoping

---

### STEP 2: Multi-Tenancy Migrations (Est. 25 min)

**Tasks:**
1. Create migration: `create_teams_table`
   - Columns: `id`, `owner_id` (FK User), `name`, `subscription_plan`, `created_at`, `updated_at`
2. Create migration: `create_team_user_table` (pivot for many-to-many)
   - Columns: `id`, `user_id` (FK), `team_id` (FK), `role_id` (FK to roles from Shield), `joined_at`
   - Unique constraint on (user_id, team_id)
3. Create migration: `create_roles_and_permissions_tables` (Filament Shield will do this in Step 4)
   - Skip for now, Shield:install will create these

**Files:**
- `database/migrations/YYYY_MM_DD_create_teams_table.php`
- `database/migrations/YYYY_MM_DD_create_team_user_table.php`

**Run:** `php artisan migrate`

---

### STEP 3: Auth Domain - Models & Relationships (Est. 35 min)

**Tasks:**
1. Move/refactor `User` model to `Domains/Auth/Models/User.php`
   - Update namespace
   - Add relationship: `belongsToMany(Team::class, 'team_user')->withPivot('role_id')->withTimestamps()`
   - Add helper method: `currentTeam()` (gets team from session/request context)
   - Add method: `hasTeamAccess(Team $team)` (check if user belongs to team)

2. Create `Domains/Auth/Models/Team.php`
   - Relationships:
     - `belongsToMany(User::class, 'team_user')->withPivot('role_id')->withTimestamps()`
     - `belongsTo(User::class, 'owner_id')` (team owner)
   - Methods: `addUser(User $user, Role $role)`, `removeUser(User $user)`, `changeUserRole(User $user, Role $role)`

3. Create `Domains/Auth/Models/TeamUser.php` (pivot model)
   - Relationships: `belongsTo(User::class)`, `belongsTo(Team::class)`, `belongsTo(Role::class)`
   - Allows typed pivot access

4. Create `Domains/Auth/Models/TeamInvitation.php` (future use)
   - Structure only for now (implementation Phase 2)

5. Update `app/Models/User.php` to extend `Domains\Auth\Models\User`
   - Keep as alias for backwards compatibility with Fortify

**Files:**
- `Domains/Auth/Models/User.php` (moved + enhanced)
- `Domains/Auth/Models/Team.php` (new)
- `Domains/Auth/Models/TeamUser.php` (new, pivot model)
- `Domains/Auth/Models/TeamInvitation.php` (structure only)

**Config:**
- Update `config/auth.php` to use `Domains\Auth\Models\User`

**Tests:**
- Feature test: User can be added to team with role
- Feature test: User with role can access team resources
- Feature test: User cannot access team without membership
- Feature test: Different roles on different teams

---

### STEP 4: Filament Shield Setup (Est. 45 min)

**Tasks:**
1. Install Filament Shield: `composer require bezhansalleh/filament-shield`
2. Run: `php artisan shield:install`
   - Creates permissions and roles tables
   - Generates base permissions
3. Create role seeder: `database/seeders/RoleAndPermissionSeeder.php`
   - Define 4 roles: Super Admin, Farm Manager, Partner, Field Worker
   - Assign permissions to each role
4. Register seeder in `database/seeders/DatabaseSeeder.php`
5. Create super admin user via seeder or command

**Files:**
- `database/seeders/RoleAndPermissionSeeder.php`
- `database/seeders/UserSeeder.php` (creates test users with different roles)

**Commands to Run:**
```bash
php artisan migrate
php artisan shield:install
php artisan db:seed --class=RoleAndPermissionSeeder
php artisan db:seed --class=UserSeeder
```

**Tests:**
- Unit test: Verify roles have correct permissions
- Feature test: Super Admin can access all resources

---

### STEP 5: CRM Domain - Models & Filament Resources (Est. 1 hour)

**Tasks:**
1. Create migrations:
   - `create_customers_table` (name, email, type, team_id, credit_limit, payment_terms)
   - `create_suppliers_table` (name, email, category, team_id, performance_rating)

2. Create models:
   - `Domains/CRM/Models/Customer.php` (use BelongsToTeam trait)
   - `Domains/CRM/Models/Supplier.php` (use BelongsToTeam trait)

3. Create Filament Resources:
   - `app/Filament/Resources/CustomerResource.php`
     - List, Create, Edit, Delete actions
     - Columns: name, email, type, credit_limit
     - Filters: by type
     - Search: by name/email
   - `app/Filament/Resources/SupplierResource.php`
     - List, Create, Edit, Delete actions
     - Columns: name, email, category, performance_rating
     - Filters: by category
     - Search: by name

**Files:**
- `database/migrations/YYYY_MM_DD_create_customers_table.php`
- `database/migrations/YYYY_MM_DD_create_suppliers_table.php`
- `Domains/CRM/Models/Customer.php`
- `Domains/CRM/Models/Supplier.php`
- `app/Filament/Resources/CustomerResource.php`
- `app/Filament/Resources/SupplierResource.php`

**Tests:**
- Feature test: Farm Manager can create/edit/delete customers
- Feature test: Partner can view but not edit customers
- Feature test: Data isolation - one team cannot see another's customers

---

### STEP 6: Finance Domain - Expenses & Basic Structure (Est. 1 hour)

**Tasks:**
1. Create migration: `create_expenses_table`
   - Columns: id, team_id, amount (big int for cents), currency, category, allocatable_type, allocatable_id, ocr_data (JSON), receipt_path, created_at, updated_at
   - Indexes: team_id, allocatable_type+allocatable_id

2. Create models:
   - `Domains/Finance/Models/Expense.php`
     - Use BelongsToTeam trait
     - Polymorphic relationship: `morphTo('allocatable')`
     - Methods: `forBatch()`, `forGeneral()`

3. Create Filament Resource:
   - `app/Filament/Resources/ExpenseResource.php`
     - List: team_id filtered, show amount/category/allocatable
     - Create: form for category, amount, allocatable
     - Edit, Delete
     - Filter by category, date range

4. Create Contracts/Interfaces:
   - `Domains/Shared/Contracts/ReceiptScanner.php` (define interface)
   - `Domains/Finance/Drivers/OcrSpaceScanner.php` (stub for dev)

**Files:**
- `database/migrations/YYYY_MM_DD_create_expenses_table.php`
- `Domains/Finance/Models/Expense.php`
- `Domains/Finance/Enums/ExpenseCategory.php`
- `Domains/Shared/Contracts/ReceiptScanner.php`
- `Domains/Shared/Contracts/Allocatable.php`
- `app/Filament/Resources/ExpenseResource.php`
- `Domains/Finance/Drivers/OcrSpaceScanner.php` (stub)

**Tests:**
- Feature test: User can create expense
- Feature test: Polymorphic allocation (expense can link to batch)
- Unit test: Expense filtering by team

---

### STEP 7: User Management in Filament (Est. 45 min)

**Tasks:**
1. Create Filament Resource: `app/Filament/Resources/UserResource.php`
   - List: show name, email, team, role
   - Create: email, name, team selection, role assignment
   - Edit: update fields, change role
   - Delete: soft delete or hard delete (with confirmation)
   - Filters: by team, by role

2. Update form validation:
   - Use Fortify's `CreateNewUser` action or create new form request
   - Password hashing
   - Email uniqueness

3. Team Resource: `app/Filament/Resources/TeamResource.php`
   - List: team name, owner, created date
   - Create: for Super Admin to create new teams
   - Edit: change owner, subscription plan
   - Delete: with validation (non-empty team protection)

**Files:**
- `app/Filament/Resources/UserResource.php`
- `app/Filament/Resources/TeamResource.php`
- `Domains/Auth/Requests/CreateUserRequest.php` (form validation)

**Tests:**
- Feature test: Super Admin can create users
- Feature test: Farm Manager can only create users in their team
- Feature test: Field Worker cannot create users
- Feature test: Users are assigned to teams correctly

---

### STEP 8: Policies & Authorization (Est. 30 min)

**Tasks:**
1. Create policies:
   - `Domains/Auth/Policies/UserPolicy.php`
     - Super Admin can do anything
     - Farm Manager can manage users in their team only
     - Others have view-only or no access
   - `Domains/CRM/Policies/CustomerPolicy.php`
     - Team-based access
   - `Domains/Finance/Policies/ExpensePolicy.php`
     - Team-based access

2. Register policies in `app/Providers/AuthServiceProvider.php`

3. Apply policies to Filament Resources using authorizeResourceUsing()

**Files:**
- `Domains/Auth/Policies/UserPolicy.php`
- `Domains/Auth/Policies/TeamPolicy.php`
- `Domains/CRM/Policies/CustomerPolicy.php`
- `Domains/CRM/Policies/SupplierPolicy.php`
- `Domains/Finance/Policies/ExpensePolicy.php`
- Updates to `app/Providers/AuthServiceProvider.php`

**Tests:**
- Unit test: Policies return correct authorization
- Feature test: Filament Resources respect authorization

---

### STEP 9: Seeders & Database Fixtures (Est. 30 min)

**Tasks:**
1. Create seeders:
   - `TeamSeeder.php` - Create test teams (e.g., "Kenna's Farm", "Test Farm")
   - `UserSeeder.php` - Create users with different roles
   - `CustomerSeeder.php` - Create sample customers
   - `SupplierSeeder.php` - Create sample suppliers
   - `ExpenseSeeder.php` - Create sample expenses

2. Update `DatabaseSeeder.php` to call all seeders in correct order

3. Make seeders conditional on `app()->isLocal()` for safety

**Files:**
- `database/seeders/TeamSeeder.php`
- `database/seeders/UserSeeder.php`
- `database/seeders/CustomerSeeder.php`
- `database/seeders/SupplierSeeder.php`
- `database/seeders/ExpenseSeeder.php`
- Updates to `database/seeders/DatabaseSeeder.php`

**Commands:**
```bash
php artisan migrate:fresh --seed
```

---

### STEP 10: Testing & Documentation (Est. 1 hour)

**Tasks:**
1. Write comprehensive tests:
   - Feature tests for all Filament Resources
   - Tests for multi-tenancy (one team can't access another's data)
   - Authorization tests for each role
   - Relationship tests

2. Update CLAUDE.md with:
   - Domain structure guide
   - How to create new resources
   - Testing examples

3. Create example Pest tests in comments

**Files:**
- `tests/Feature/Admin/UserResourceTest.php`
- `tests/Feature/Admin/CustomerResourceTest.php`
- `tests/Feature/Admin/SupplierResourceTest.php`
- `tests/Feature/Admin/ExpenseResourceTest.php`
- `tests/Feature/Auth/AuthorizationTest.php`
- Updates to `CLAUDE.md`

**Commands:**
```bash
php artisan test
```

---

## Database Schema Summary

### Tables to Create/Modify

```sql
-- teams (new)
teams:
  id (PK)
  owner_id (FK users)
  name (string)
  subscription_plan (enum: Basic, Pro, Enterprise)
  timestamps

-- team_user (new - pivot table)
team_user:
  id (PK)
  user_id (FK users)
  team_id (FK teams)
  role_id (FK roles) -- from Filament Shield
  joined_at (timestamp)
  unique(user_id, team_id)

-- users (no changes needed)
-- Keep as is, no team_id column

-- customers (new)
customers:
  id (PK)
  team_id (FK teams)
  name (string)
  email (string)
  type (enum: Wholesale, Retail)
  credit_limit (big int)
  payment_terms (string)
  timestamps

-- suppliers (new)
suppliers:
  id (PK)
  team_id (FK teams)
  name (string)
  email (string)
  category (enum: Feed, Chicks, Meds)
  performance_rating (decimal)
  timestamps

-- expenses (new)
expenses:
  id (PK)
  team_id (FK teams)
  amount (big int) [stored as cents/thebe]
  currency (string, default: BWP)
  category (string)
  allocatable_type (string)
  allocatable_id (int)
  ocr_data (json, nullable)
  receipt_path (string, nullable)
  timestamps

-- Shield tables (auto-created by shield:install)
roles, permissions, role_has_permissions, model_has_roles, model_has_permissions
```

---

## Implementation Order

**Day 1:**
1. Step 1: Domain structure (30 min)
2. Step 2: Migrations (25 min) - Now includes team_user pivot
3. Step 3: Auth models (35 min) - Now many-to-many relationships

**Day 2:**
4. Step 4: Filament Shield (45 min)
5. Step 5: CRM domain (1 hour)

**Day 3:**
6. Step 6: Finance domain (1 hour)
7. Step 7: User management (1 hour) - Now includes team switcher in Filament

**Day 4:**
8. Step 8: Policies (30 min)
9. Step 9: Seeders (45 min) - Seed team memberships with roles
10. Step 10: Tests & docs (1.5 hours) - More tests for many-to-many

**Total Estimated Time: ~8.5 hours of focused development**

---

## Key Decisions Requiring Review

### Decision 1: User Team Assignment ✅ DECIDED
**Question:** Should a user belong to one team or many teams?
- **Your Preference:** Many teams with different roles per team
- **Implementation:** Many-to-many via `team_user` pivot table with role_id
- **Benefit:** Users can work across multiple farms with different permissions on each

**This is now locked in the plan.**

---

### Decision 2: Current Team Context
**Question:** How do we handle "current team" when user belongs to multiple teams?
- **Option A:** Middleware sets current_team from query param or session
- **Option B:** User selects team at login (team switcher in UI)
- **Recommended:** Both - middleware from session, team switcher for Filament

**Note:** The `BelongsToTeam` trait will filter by current_team context, not user's teams.

---

### Decision 3: Role Assignment Storage
**Question:** Where should role information be stored?
- **Current Plan:** In `team_user` pivot table (role_id column)
- **Alternative:** In spatie/laravel-permission with team namespace

**Recommendation:** Use pivot table for simplicity. Filament Shield will manage permissions, pivot stores role assignment.

---

### Decision 4: OCR Integration
**Question:** Should we implement full OCR now or stub it for Phase 2?
- **Current Plan:** Create interface + stub driver now, full implementation in Phase 2
- **Alternative:** Full implementation now with OcrSpace API

**Recommendation:** Stub now (interface-first design). Implement drivers in Phase 2 when Expense feature is complete.

---

### Decision 4: Currency Handling
**Question:** Use `laravel-money` package or native integer columns?
- **Current Plan:** Native integers (amount stored as cents/thebe)
- **Alternative:** Use `cknow/laravel-money` value objects

**Recommendation:** Native integers for now. Refactor to Money package in Phase 2 when Finance module grows.

---

## Risk Mitigation

| Risk | Mitigation |
|------|-----------|
| Team scope leaking | Aggressive testing of BelongsToTeam trait with multiple teams |
| Complex migrations | Test migrations in fresh database, include rollback tests |
| Authorization bugs | Write authorization tests before resources |
| Namespace conflicts | Update composer autoload, test imports thoroughly |
| Filament Shield complexity | Follow official docs, implement incrementally |

---

## Success Criteria

✓ Phase 1 is complete when:
- [ ] Domain structure created and working
- [ ] Multi-tenancy active (teams table, team_id on users)
- [ ] Filament Shield installed with 4 roles defined
- [ ] User, Team, Customer, Supplier resources fully functional
- [ ] Expense model created with polymorphic relationships
- [ ] All Filament resources respect team-based authorization
- [ ] Comprehensive tests for all features (80%+ coverage)
- [ ] Data isolation verified (one team can't see another's data)
- [ ] Documentation updated with domain structure
- [ ] All tests passing (php artisan test)
- [ ] Zero ESLint/TypeScript errors (npm run lint && npm run types)

---

## Next Steps

1. **Review this plan** - Does it align with your vision?
2. **Clarify decisions** - Any preferred approaches to the 4 key decisions above?
3. **Approval** - Ready to proceed with Step 1?
4. **Track progress** - Use git commits with descriptive messages

---

**Plan Version:** 1.0
**Created:** 2025-12-02
**Status:** Ready for Review
