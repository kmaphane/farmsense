# DTO Implementation - Testing Checklist

## 🚀 Step 1: Install Package

```bash
composer require spatie/laravel-data
```

**Expected Output:**
- Package installs successfully
- No conflicts or errors
- `vendor/spatie/laravel-data/` directory created

## 🧪 Step 2: Run Automated Tests

### Run Full Test Suite
```bash
php artisan test
```

**Expected Output:**
- All existing tests pass
- No new failures introduced

### Run DTO-Specific Tests
```bash
# Run all DTO tests
php artisan test --filter=DataTest

# Run CRM DTO tests
php artisan test Domains/CRM/tests/Unit/DTOs/

# Run Finance DTO tests
php artisan test Domains/Finance/tests/Unit/DTOs/

# Run Inventory DTO tests
php artisan test Domains/Inventory/tests/Unit/DTOs/
```

**Expected Output:**
- ✅ CustomerDataTest - 7 tests pass
- ✅ ExpenseDataTest - 4 tests pass
- ✅ ProductDataTest - 5 tests pass

## 🖥️ Step 3: Manual Testing - Filament Admin

### Test 1: Create Customer
1. Navigate to `http://farmsense.test/admin/customers/create`
2. Fill in the form:
   - **Name:** "Test Customer"
   - **Email:** "test@customer.com"
   - **Phone:** "1234567890"
   - **Type:** "Wholesale"
   - **Credit Limit:** 50000
3. Click "Create"

**Expected Result:**
- ✅ Customer created successfully
- ✅ `team_id` auto-filled from current user
- ✅ Redirects to customer list
- ✅ Check database: `SELECT * FROM customers ORDER BY id DESC LIMIT 1;`

### Test 2: Edit Customer
1. Click "Edit" on the customer you just created
2. Change **Name** to "Updated Customer"
3. Click "Save"

**Expected Result:**
- ✅ Customer updated successfully
- ✅ Name changed in database
- ✅ No validation errors

### Test 3: Create Expense
1. Navigate to `http://farmsense.test/admin/expenses/create`
2. Fill in the form:
   - **Amount:** 500.00 (in BWP)
   - **Category:** "Feed"
   - **Description:** "Monthly feed purchase"
3. Click "Create"

**Expected Result:**
- ✅ Expense created successfully
- ✅ Amount stored as 50000 (cents conversion works)
- ✅ `team_id` auto-filled
- ✅ Check database: `SELECT amount FROM expenses ORDER BY id DESC LIMIT 1;` → Should be 50000

### Test 4: Create Product
1. Navigate to `http://farmsense.test/admin/products/create`
2. Fill in the form:
   - **Name:** "Starter Feed"
   - **Type:** "feed"
   - **Unit:** "kg"
   - **Quantity:** 1000
   - **Unit Cost:** 25.00
3. Click "Create"

**Expected Result:**
- ✅ Product created successfully
- ✅ `team_id` auto-filled
- ✅ `is_active` defaults to true
- ✅ `quantity_on_hand` set to 0 (default)

### Test 5: Validation Testing
1. Navigate to `http://farmsense.test/admin/customers/create`
2. Try to submit with:
   - **Name:** (empty)
   - **Email:** "invalid-email"
   - **Type:** Not selected

**Expected Result:**
- ❌ Form validation errors displayed
- ✅ Shows: "Name is required"
- ✅ Shows: "Email must be valid"
- ✅ Shows: "Type is required"

## 🌐 Step 4: Manual Testing - React Frontend

### Test 6: Profile Update
1. Navigate to `http://farmsense.test/settings/profile`
2. Update:
   - **Name:** "Updated Name"
   - **Email:** Keep current email
3. Click "Save"

**Expected Result:**
- ✅ Profile updated successfully
- ✅ DTO validation works
- ✅ No errors
- ✅ Success message displayed

### Test 7: Profile Validation
1. Navigate to `http://farmsense.test/settings/profile`
2. Try to update email to existing user's email
3. Click "Save"

**Expected Result:**
- ❌ Validation error: "Email already taken"
- ✅ DTO unique validation rule works

## 🔍 Step 5: Database Verification

### Verify Team Scoping
```sql
-- All customers should have team_id
SELECT id, name, team_id FROM customers WHERE team_id IS NULL;
-- Should return 0 rows

-- All expenses should have team_id
SELECT id, description, team_id FROM expenses WHERE team_id IS NULL;
-- Should return 0 rows

-- All products should have team_id
SELECT id, name, team_id FROM products WHERE team_id IS NULL;
-- Should return 0 rows
```

### Verify Currency Conversion
```sql
-- Check expense amounts are in cents
SELECT id, description, amount, currency FROM expenses ORDER BY id DESC LIMIT 5;
-- All amounts should be integers (cents)
```

### Verify Defaults
```sql
-- Check product defaults
SELECT id, name, quantity_on_hand, is_active FROM products ORDER BY id DESC LIMIT 5;
-- quantity_on_hand should default to 0
-- is_active should default to true (1)
```

## 📊 Step 6: IDE Verification

### Check for Warnings
Open these files in your IDE and verify NO warnings:

- ✅ `app/Filament/Resources/CustomerResource/Pages/CreateCustomer.php`
- ✅ `app/Filament/Resources/ExpenseResource/Pages/CreateExpense.php`
- ✅ `app/Http/Controllers/Settings/ProfileController.php`

**Expected Result:**
- ✅ No "Call to unknown method: toArray()" warnings
- ✅ No "Use of unknown class" warnings
- ✅ Full autocomplete for DTO properties

## 🎯 Step 7: Performance Check

### Check Query Performance
```bash
# Enable query logging temporarily
DB_QUERY_LOG=true php artisan serve
```

Create a customer and check:
- ✅ No N+1 queries
- ✅ Expected number of queries (typically 2-3: select auth, insert customer)

## ✅ Success Criteria

All tests pass when:

- [x] Package installed without errors
- [ ] All automated tests pass (16+ tests)
- [ ] Can create records in Filament admin
- [ ] Can edit records in Filament admin
- [ ] Validation works correctly (rejects invalid data)
- [ ] `team_id` auto-filled correctly
- [ ] Currency conversion works (expenses in cents)
- [ ] Profile update works on React frontend
- [ ] No IDE warnings
- [ ] Database records have correct data

## 🐛 Troubleshooting

### Issue: "Call to undefined method toArray()"
**Solution:** Package not installed. Run `composer require spatie/laravel-data`

### Issue: "Validation Exception" on valid data
**Solution:** Check DTO validation attributes match model fillable fields

### Issue: `team_id` is NULL
**Solution:** Ensure user is authenticated and has `current_team_id` set

### Issue: Amount not converting to cents
**Solution:** Check `CreateExpense.php` has currency conversion before DTO creation

### Issue: Tests failing
**Solution:**
1. Run `composer dump-autoload`
2. Clear cache: `php artisan config:clear`
3. Re-run tests with verbose output: `php artisan test --filter=DataTest --verbose`

## 📝 Post-Testing Actions

After all tests pass:

1. **Commit Changes:**
   ```bash
   git add .
   git commit -m "✨ 📦 Implement DTOs with Spatie Laravel Data

   - Add BaseData with team_id helpers
   - Create DTOs for CRM, Finance, Inventory, Auth domains
   - Update all Filament Resource Create/Edit pages to use DTOs
   - Update ProfileController to use ProfileUpdateData
   - Add comprehensive Pest tests for DTOs
   - Create documentation for DTO usage

   🤖 Generated with [Claude Code](https://claude.com/claude-code)

   Co-Authored-By: Claude <noreply@anthropic.com>"
   ```

2. **Optional Cleanup:**
   ```bash
   # Remove old Form Request classes (if desired)
   rm app/Http/Requests/Settings/ProfileUpdateRequest.php
   rm app/Http/Requests/Settings/TwoFactorAuthenticationRequest.php
   ```

3. **Update CLAUDE.md** (optional):
   Add DTO best practices to the project guidelines

---

**Testing Date:** _________________
**Tester:** _________________
**Status:** ⏳ Pending package installation
