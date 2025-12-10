# Phase 3D: Complete CRUD Implementation Plan

**Status:** Ready to Execute
**Estimated Time:** 12-16 hours
**Priority:** HIGH - Fixing broken navigation

---

## Implementation Order

### Priority 1: Core Operations (4-5 hours)

1. **Slaughter Management** (1.5 hours)
2. **Live Sales Management** (1 hour)
3. **Portioning Management** (1 hour)
4. **Batch History & Logs** (1.5 hours)

### Priority 2: Inventory & CRM (4-5 hours)

5. **Stock Movements** (1.5 hours)
6. **Customer Management** (1.5 hours)
7. **Warehouse Management** (1 hour)
8. **Supplier Management** (2 hours)

### Priority 3: Polish & Testing (2-3 hours)

9. **Route Integration**
10. **Navigation Testing**
11. **Error Handling**
12. **Responsive Design**

---

## Module 1: Slaughter Management

### Files to Create

**Controller:**
- Enhance `app/Http/Controllers/Slaughter/SlaughterController.php`
  - Add `index()` - List all slaughter records with filters
  - Add `show($id)` - View single slaughter record details

**Pages:**
- `resources/js/pages/Slaughter/Index.tsx` - Slaughter records table
- `resources/js/pages/Slaughter/Show.tsx` - Slaughter details page

**Routes:**
```php
Route::get('/slaughter', [SlaughterController::class, 'index'])->name('slaughter.index');
Route::get('/slaughter/{slaughter}', [SlaughterController::class, 'show'])->name('slaughter.show');
```

**Features:**
- Table with columns: Date, Total Birds, Batches, Yields, Discrepancies, Actions
- Filters: Date range, batch
- Click row to view details
- Show page displays: batch sources, yields, stock movements created
- Link to batch show pages

---

## Module 2: Portioning Management

### Files to Create

**Controller:**
- Enhance `app/Http/Controllers/Portioning/PortioningController.php`
  - Add `index()` - List all portioning records
  - Add `show($id)` - View portioning details

**Pages:**
- `resources/js/pages/Portioning/Index.tsx` - Portioning records table
- `resources/js/pages/Portioning/Show.tsx` - Portioning details

**Routes:**
```php
Route::get('/portioning', [PortioningController::class, 'index'])->name('portioning.index');
Route::get('/portioning/{portioning}', [PortioningController::class, 'show'])->name('portioning.show');
```

**Features:**
- Table: Date, Whole Birds Used, Packs Produced, Weight, Actions
- Show page: Full details, stock movements in/out

---

## Module 3: Live Sales Management

### Files to Create

**Controller:**
- Enhance `app/Http/Controllers/LiveSales/LiveSaleController.php`
  - Add `index()` - List all live sales
  - Add `show($id)` - View sale details

**Pages:**
- `resources/js/pages/LiveSales/Index.tsx` - Live sales table
- `resources/js/pages/LiveSales/Show.tsx` - Sale details

**Routes:**
```php
Route::get('/live-sales', [LiveSaleController::class, 'index'])->name('live-sales.index');
Route::get('/live-sales/{liveSale}', [LiveSaleController::class, 'show'])->name('live-sales.show');
```

**Features:**
- Table: Date, Batch, Quantity, Unit Price, Total, Customer, Actions
- Filters: Date range, batch, customer
- Show page: Sale details with batch link

---

## Module 4: Batch History & Logs

### Files to Create

**Controller:**
- Enhance `app/Http/Controllers/Batches/BatchController.php`
  - Add `history()` - List closed batches

**New Controller:**
- `app/Http/Controllers/Batches/DailyLogIndexController.php`
  - Add `index()` - List all daily logs across batches

**Pages:**
- `resources/js/pages/Batches/History.tsx` - Closed batches with final metrics
- `resources/js/pages/Batches/Logs/Index.tsx` - All daily logs table

**Routes:**
```php
Route::get('/batches/history', [BatchController::class, 'history'])->name('batches.history');
Route::get('/batches/logs', [DailyLogIndexController::class, 'index'])->name('batches.logs');
```

**Features - History:**
- Table: Batch, Dates, Final Count, FCR, EPEF, Mortality %
- Filter by date range
- Click to view batch details

**Features - Logs:**
- Table: Date, Batch, Mortality, Feed, Water, Temp, Actions
- Filters: Batch, date range
- Link to edit log (if within 24h)

---

## Module 5: Stock Movements

### Files to Create

**Controller:**
- `app/Http/Controllers/Inventory/StockMovementController.php`
  - `index()` - List all stock movements
  - `show($id)` - View movement details

**Pages:**
- `resources/js/pages/StockMovements/Index.tsx` - Stock movements table
- `resources/js/pages/StockMovements/Show.tsx` - Movement details

**Routes:**
```php
Route::get('/inventory/movements', [StockMovementController::class, 'index'])->name('stock-movements.index');
Route::get('/inventory/movements/{movement}', [StockMovementController::class, 'show'])->name('stock-movements.show');
```

**Features:**
- Table: Date, Product, Type (In/Out), Quantity, Reason, Reference
- Filters: Product, type, date range
- Show page: Full details with product/warehouse info

---

## Module 6: Warehouse Management

### Files to Create

**Controller:**
- `app/Http/Controllers/Inventory/WarehouseController.php`
  - `index()` - List all warehouses
  - `show($id)` - Warehouse details with stock levels

**Pages:**
- `resources/js/pages/Warehouses/Index.tsx` - Warehouses list
- `resources/js/pages/Warehouses/Show.tsx` - Warehouse stock levels

**Routes:**
```php
Route::get('/inventory/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
Route::get('/inventory/warehouses/{warehouse}', [WarehouseController::class, 'show'])->name('warehouses.show');
```

**Features:**
- Table: Name, Location, Products Count, Actions
- Show page: Stock levels per product in this warehouse

---

## Module 7: Customer Management

### Files to Create

**Controller:**
- Enhance `app/Http/Controllers/CRM/CustomerController.php`
  - Add `index()` - List all customers
  - Add `show($id)` - Customer details
  - Add `edit($id)` - Edit form
  - Add `update($id)` - Update customer
  - Add `destroy($id)` - Delete customer

**Pages:**
- `resources/js/pages/Customers/Index.tsx` - Customers table
- `resources/js/pages/Customers/Show.tsx` - Customer details & sales history
- `resources/js/pages/Customers/Edit.tsx` - Edit customer form

**Routes:**
```php
Route::get('/crm/customers', [CustomerController::class, 'index'])->name('customers.index');
Route::get('/crm/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
Route::get('/crm/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
Route::patch('/crm/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
Route::delete('/crm/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
```

**Features:**
- Table: Name, Type, Phone, Email, Credit Limit, Actions
- Filters: Type (Wholesale/Retail)
- Show page: Customer info + live sales history
- Edit page: Update customer details

---

## Module 8: Supplier Management

### Files to Create

**Controller:**
- `app/Http/Controllers/CRM/SupplierController.php` (Full CRUD)
  - `index()`, `create()`, `store()`, `show()`, `edit()`, `update()`, `destroy()`

**Pages:**
- `resources/js/pages/Suppliers/Index.tsx` - Suppliers table
- `resources/js/pages/Suppliers/Create.tsx` - Create supplier form
- `resources/js/pages/Suppliers/Show.tsx` - Supplier details
- `resources/js/pages/Suppliers/Edit.tsx` - Edit supplier form

**Routes:**
```php
Route::resource('crm/suppliers', SupplierController::class);
```

**Features:**
- Full CRUD interface
- Table: Name, Type, Contact, Rating, Actions
- Filters: Type (Feed/Chick/Medicine)
- Show page: Supplier info + batch history (chicks supplied)

---

## Navigation Updates

### Update `app-sidebar.tsx`

Current broken links and their fixes:

| Current (Broken) | New Route | Status |
|------------------|-----------|--------|
| `/batches/history` | `batches.history` | ✅ Will create |
| `/batches/logs` | `batches.logs` | ✅ Will create |
| `/batches/slaughter` | `slaughter.index` | ✅ Change route |
| `/batches/product-yield` | Remove or defer | ⚠️ TBD |
| `/inventory/movements` | `stock-movements.index` | ✅ Will create |
| `/inventory/warehouses` | `warehouses.index` | ✅ Will create |
| `/crm/customers` | `customers.index` | ✅ Will create |
| `/crm/suppliers` | `suppliers.index` | ✅ Will create |
| `/live-sales` | `live-sales.index` | ✅ Will create |

### Sidebar Reorganization

```typescript
const navGroups: NavGroup[] = [
    {
        title: 'Batches',
        items: [
            { title: 'Active Batches', href: '/batches', icon: Bird },
            { title: 'Batch History', href: '/batches/history', icon: ClipboardList },
            { title: 'Daily Logs', href: '/batches/logs', icon: FileText },
        ],
    },
    {
        title: 'Operations',
        items: [
            { title: 'Slaughter', href: '/slaughter', icon: Scissors },
            { title: 'Portioning', href: '/portioning', icon: Package },
            { title: 'Live Sales', href: '/live-sales', icon: ShoppingCart },
        ],
    },
    {
        title: 'Inventory',
        items: [
            { title: 'Products', href: '/inventory/products', icon: Package },
            { title: 'Stock Movements', href: '/inventory/movements', icon: TrendingUp },
            { title: 'Warehouses', href: '/inventory/warehouses', icon: Building2 },
        ],
    },
    {
        title: 'CRM',
        items: [
            { title: 'Customers', href: '/crm/customers', icon: Users },
            { title: 'Suppliers', href: '/crm/suppliers', icon: Layers },
        ],
    },
];
```

---

## Testing Checklist

After implementation, verify:

- [ ] All 15 navigation links work (no 404s)
- [ ] Each index page loads with data
- [ ] Each show page displays record details
- [ ] Filters work on index pages
- [ ] Create forms (Quick Actions) still work
- [ ] Edit forms work where applicable
- [ ] Delete actions work with confirmation
- [ ] Mobile responsive on all pages
- [ ] Loading states display correctly
- [ ] Error states handled gracefully

---

## Implementation Notes

### Shared Components to Create

1. **DataTable Component** - Reusable table with sorting/filtering
2. **FilterBar Component** - Consistent filter UI
3. **DetailCard Component** - Consistent detail page layout
4. **EmptyState Component** - "No records found" state

### Styling Consistency

- Use existing shadcn/ui Table component
- Follow Batches/Index.tsx pattern for layouts
- Use Sheet for filters (mobile-friendly)
- Consistent spacing and typography

### Performance

- Paginate all index pages (default 15 per page)
- Eager load relationships to avoid N+1
- Use Inertia partial reloads for filters
- Add loading skeletons

---

## Execution Order

**Session 1 (4-5 hours):**
1. Slaughter CRUD
2. Live Sales CRUD
3. Portioning CRUD
4. Batch History

**Session 2 (4-5 hours):**
5. Stock Movements CRUD
6. Customer CRUD
7. Warehouse CRUD
8. Daily Logs Index

**Session 3 (3-4 hours):**
9. Supplier full CRUD
10. Navigation updates
11. Testing & bug fixes
12. Code formatting

---

**Ready to begin implementation!**
