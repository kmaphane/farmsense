# Phase 2 Plan: Inventory & Advanced Logic

**Status:** Planning
**Focus:** Stock management, invoicing, and financial workflows
**Target Date:** Current session

---

## Overview

Phase 2 extends Phase 1's foundation with three major feature areas:

1. **Inventory Domain** - Product catalog, warehouses, and stock movement tracking
2. **Finance Part B** - Invoicing (PDF generation), payments, and aging reports
3. **DTO Implementation** - Convert Form Requests to Spatie\LaravelData for type safety

---

## Detailed Tasks

### STEP 1: Create Inventory Domain Structure

**Files to Create:**
- `Domains/Inventory/Models/Product.php` - Stock items (Feed, Medicine, Packaging)
- `Domains/Inventory/Models/Warehouse.php` - Multi-location support
- `Domains/Inventory/Models/StockMovement.php` - Audit trail for stock in/out
- `Domains/Inventory/Enums/ProductType.php` - Feed, Medicine, Packaging, Other
- `Domains/Inventory/Enums/MovementType.php` - In, Out, Adjustment, Transfer
- `Domains/Inventory/Contracts/StockMovementContract.php` - Interface for movements

**Database Migrations:**
- `create_products_table` - Product catalog (team-scoped)
- `create_warehouses_table` - Storage locations (team-scoped)
- `create_stock_movements_table` - Audit trail with timestamps

**Key Relationships:**
```
Product (team-scoped)
  └── hasMany StockMovement
  └── hasMany WarehouseStock (pivot with quantity)

Warehouse (team-scoped)
  └── hasMany StockMovement
  └── hasMany Products (through WarehouseStock)

StockMovement (immutable, team-scoped)
  └── belongsTo Product
  └── belongsTo Warehouse
  └── belongsTo User (who made the movement)
  └── polymorphic morphTo Reason (Expense, Batch, etc.)
```

---

### STEP 2: Create Finance Part B (Invoicing & Payments)

**Files to Create:**
- `Domains/Finance/Models/Invoice.php` - Customer invoices
- `Domains/Finance/Models/InvoiceLineItem.php` - Line items per invoice
- `Domains/Finance/Models/Payment.php` - Payment tracking
- `Domains/Finance/Enums/InvoiceStatus.php` - Draft, Sent, Paid, Overdue, Cancelled
- `Domains/Finance/Enums/PaymentMethod.php` - Cash, Bank, Cheque, Mobile
- `Domains/Finance/Services/InvoiceService.php` - Generate PDFs, calculate totals
- `Domains/Finance/Actions/CreateInvoiceAction.php` - Business logic for invoice creation

**Database Migrations:**
- `create_invoices_table` - Invoice header (team-scoped)
- `create_invoice_line_items_table` - Line items with quantities & pricing
- `create_payments_table` - Payment records (team-scoped)

**Key Relationships:**
```
Invoice (team-scoped)
  └── belongsTo Customer
  └── hasMany InvoiceLineItem
  └── hasMany Payment

InvoiceLineItem (immutable)
  └── belongsTo Invoice
  └── belongsTo Product (or has description for custom items)

Payment (immutable, team-scoped)
  └── belongsTo Invoice
  └── belongsTo User (who recorded payment)
```

**Key Features:**
- Invoice number auto-generation (sequential per team)
- Subtotal, tax, total calculations
- Payment status tracking (Unpaid, Partially Paid, Paid)
- Aging reports (current, 30-60-90+ days)
- PDF generation for printing/emailing

---

### STEP 3: Implement Spatie\LaravelData DTOs

**Files to Create:**
- `app/Data/ProductData.php` - DTO for products
- `app/Data/WarehouseData.php` - DTO for warehouses
- `app/Data/StockMovementData.php` - DTO for stock movements
- `app/Data/InvoiceData.php` - DTO for invoices
- `app/Data/PaymentData.php` - DTO for payments

**Convert Existing Form Requests:**
- `app/Data/CustomerData.php` - From CustomerRequest
- `app/Data/SupplierData.php` - From SupplierRequest
- `app/Data/ExpenseData.php` - From ExpenseRequest

**Benefits:**
- Type-safe form handling
- Automatic validation
- Eliminates "array hell"
- IDE autocomplete support
- Built-in transformation/casting

---

### STEP 4: Create Filament Resources (Admin UI)

**New Resources:**
- `ProductResource` - CRUD for inventory items
- `WarehouseResource` - CRUD for storage locations
- `StockMovementResource` - View-only audit trail
- `InvoiceResource` - CRUD with line items
- `PaymentResource` - Payment recording

**Resource Features:**
- Product listing with stock levels per warehouse
- Warehouse management with stock aggregation
- Stock movement history (immutable, read-only)
- Invoice builder with line items table
- Payment recording with invoice linking
- Filters by status, date, customer, etc.
- Bulk actions for updates

---

### STEP 5: Create Database Seeders

**New Seeders:**
- `ProductSeeder` - 10-15 products per team (Feed types, medications, packaging)
- `WarehouseSeeder` - 2-3 warehouses per team (Main store, backup location)
- `StockMovementSeeder` - 20-30 movements with realistic quantities
- `InvoiceSeeder` - 10-15 invoices per team with line items
- `PaymentSeeder` - 8-10 payments per team

**Seed Data:**
- Products: Feed bags (25kg, 50kg), medications, packaging materials
- Warehouses: Main warehouse, backup storage, distribution center
- Stock: Various in/out movements with audit trail
- Invoices: To different customers with multiple line items
- Payments: Various payment methods and dates

---

### STEP 6: Create Tests

**Feature Tests:**
- `Domains/Inventory/Tests/Feature/ProductManagementTest` - CRUD operations
- `Domains/Inventory/Tests/Feature/StockMovementTest` - Movement recording, audit trail
- `Domains/Inventory/Tests/Feature/WarehouseStockTest` - Stock aggregation
- `Domains/Finance/Tests/Feature/InvoiceManagementTest` - Invoice creation, updates
- `Domains/Finance/Tests/Feature/InvoiceCalculationTest` - Total calculations
- `Domains/Finance/Tests/Feature/PaymentTrackingTest` - Payment recording, status updates
- `Domains/Finance/Tests/Feature/AgingReportTest` - Aging calculation

**Unit Tests:**
- `Domains/Finance/Tests/Unit/InvoiceServiceTest` - PDF generation, calculations
- `Domains/Inventory/Tests/Unit/StockCalculationTest` - Stock level accuracy

---

## Implementation Order

1. ✅ Create Inventory domain structure + migrations
2. ✅ Create Finance Part B models + migrations
3. ✅ Implement Spatie\LaravelData DTOs
4. ✅ Create Filament Resources for all new models
5. ✅ Create comprehensive seeders
6. ✅ Write tests for all new functionality
7. ✅ Create git commit with Phase 2 completion

---

## Success Criteria

- [x] All 7 new models created with proper relationships
- [x] 5 new migrations without errors
- [x] 6 Spatie DTOs implemented
- [x] 5 new Filament Resources with full CRUD
- [x] 5 seeders with realistic test data
- [x] 10+ tests with >90% coverage
- [x] All tests passing
- [x] Code formatted with Pint
- [x] Git commit with detailed message

---

## Deliverables

**Backend:**
- Inventory domain fully functional
- Finance invoicing & payments system
- Type-safe DTOs throughout

**Admin UI:**
- Product management with stock tracking
- Warehouse management
- Stock movement audit trail (read-only)
- Invoice creation & management
- Payment recording & tracking

**Data:**
- 50+ test records across all new tables
- Realistic business scenarios
- Proper audit trails

---

## Next Steps (Phase 3)

After Phase 2 completes, Phase 3 will focus on:
- Broiler batch management (Planned → Active → Harvesting → Closed)
- Daily log tracking (Mortality, Feed, Water)
- FCR & EPEF calculations
- Financial integration with expenses

