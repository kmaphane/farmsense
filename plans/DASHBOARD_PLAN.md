# Farm Manager's Morning Coffee Dashboard - Implementation Plan

**Date:** 2025-12-10
**Status:** Planning - Deferred until Phase 3 CRUD pages complete
**Objective:** Create a farmer-focused dashboard that provides the critical "pulse points" a farm manager needs every morning

---

## Executive Summary

The current dashboard ([resources/js/pages/Dashboard.tsx](resources/js/pages/Dashboard.tsx:78)) is production-focused (active batches, FCR, mortality). However, we're missing the **business intelligence** layer that answers the farmer's core questions:

1. **"How much money is sitting in the coop?"** (Stock value, pending sales)
2. **"Who owes me money?"** (Outstanding debts)
3. **"Are the chickens alive and comfortable?"** (Live pulse)
4. **"Do I have enough inputs?"** (Feed, bedding, meds)
5. **"Can I afford the next cycle?"** (Budget projections)

This plan reorganizes the dashboard into **4 Intelligence Zones** (Cashflow → Operations → Inventory → Planning) and identifies critical gaps in our current Phase 3 implementation that prevent this vision.

---

## Current State Analysis

### ✅ What We Have

**React Dashboard** (`/dashboard`):
- 6 stat cards (Active Batches, Total Birds, Avg FCR, Avg Mortality, Today's Logs, Pending Alerts)
- Active batches display with BatchCard components
- DashboardController with team-scoped queries
- BatchCalculationService for FCR/EPEF/Mortality calculations

**Domain Models Available:**
- **Broiler:** Batch, DailyLog, SlaughterRecord, LiveSaleRecord, PortioningRecord
- **Finance:** Expense, Invoice, Payment
- **Inventory:** Product, StockMovement, Warehouse
- **CRM:** Customer, Supplier

**Existing Calculations:**
- FCR (Feed Conversion Ratio)
- EPEF (European Production Efficiency Factor)
- Mortality Rate %
- Batch age, current quantity
- Cost per bird (from Expense model)

### ❌ What's Missing

**Critical Data Gaps:**
1. **No Invoice Management** - Cannot track outstanding debts
2. **No Sales Revenue Tracking** - Live sales exist but not invoiced/tracked financially
3. **No Stock Valuation** - Cannot calculate "stock ready for sale" value
4. **No Low Stock Alerts** - Product.reorder_level not being used
5. **No Weather/Environment Data** - IoT domain empty
6. **No Feed Budget Projections** - FeedSchedule exists but not used for planning

**Missing Pages (from Gap Analysis):**
- 12 out of 15 sidebar links lead to 404s
- No Customer Index/Show pages
- No Supplier management pages
- No Stock Movement views
- No Slaughter/Portioning history pages

---

## The 4-Zone Dashboard Strategy

### Zone A: Cashflow & Sales Snapshot

**Goal:** "How much money is sitting in the coop, and how much is stuck in debtors?"

**Proposed Widgets:**

1. **Stats Overview (4 columns):**
   - **Stock Ready for Sale** - Live birds + inventory products valued at selling price
   - **Stock Sold (This Month)** - Revenue from LiveSaleRecord + invoiced products
   - **Revenue Collected** - Sum of Payment.amount where payment_date is this month
   - **Outstanding Debt** - Sum of (Invoice.total_amount - payments) where due_date passed

2. **Debtor Watchlist Table Widget:**
   - Columns: Customer Name | Amount Due | Days Overdue | Last Contact | Action
   - Filter: Only debts > P500 OR > 30 days overdue
   - Click-to-call/email actions
   - Color-coded by severity (30+ days = yellow, 60+ = red)

**Data Sources:**
- `Invoice::query()->with('customer', 'payments')->where('team_id', $teamId)`
- `Payment::query()->whereMonth('payment_date', now()->month)`
- `LiveSaleRecord::query()->whereMonth('sale_date', now()->month)->sum('total_amount_cents')`
- `Product::query()->where('type', 'live_bird')->sum('quantity_on_hand * selling_price_cents')`

**Implementation Status:**
- ❌ **BLOCKED** - Invoice model exists but no UI/controller to manage invoices
- ❌ **BLOCKED** - Payment tracking not implemented (Phase 4)
- ⚠️ **PARTIAL** - LiveSaleRecord exists with revenue data
- ✅ **READY** - Product stock levels available

---

### Zone B: Live Pulse (Production)

**Goal:** "Are the chickens alive and comfortable?"

**Proposed Widgets:**

1. **Active Batches Grid:**
   - Card-based display (already exists as BatchCard component)
   - **Enhancements needed:**
     - Color-coded mortality alerts (Green <2%, Yellow 2-4%, Red >4%)
     - "Last Logged" timestamp with "Log Now" quick action
     - Batch health score (weighted: 40% mortality, 30% FCR, 30% age compliance)

2. **Environment Sensor Widget (IoT Placeholder):**
   - 3 gauges: Temperature (°C), Humidity (%), Ammonia (ppm)
   - **Phase 3:** Manual entry from latest DailyLog
   - **Phase 6:** Auto-sync from IoT sensors
   - Weather forecast integration (free API: OpenWeatherMap based on farm lat/long)

**Data Sources:**
- `Batch::query()->whereIn('status', [Active, Harvesting])->with('dailyLogs')`
- `DailyLog::query()->latest()->first()` per batch for environmental data
- External: OpenWeatherMap API for forecast

**Implementation Status:**
- ✅ **READY** - BatchCard component exists
- ✅ **READY** - DailyLog has temperature/humidity/ammonia fields
- ❌ **MISSING** - No weather API integration
- ❌ **MISSING** - No "health score" calculation

---

### Zone C: Stockpile (Inventory)

**Goal:** "Do we have enough inputs to keep them alive?"

**Proposed Widgets:**

1. **Critical Low Stock Alert:**
   - Traffic light list showing items below reorder_level
   - Example: 🔴 **Broiler Starter Crumbles** (2 bags left - Est. 1 day supply)
   - Formula: `days_remaining = quantity_on_hand / avg_daily_consumption`
   - "Order Now" button → Pre-filled purchase order

2. **Inventory Summary Chart:**
   - Bar chart: Current stock vs Required stock for next 7 days
   - Categories: Feed (by type), Bedding, Medication
   - Stacked bars: Green (adequate) + Red (shortfall)

**Data Sources:**
- `Product::query()->where('quantity_on_hand', '<=', 'reorder_level')`
- `DailyLog::query()->avg('feed_consumed_kg')` for consumption rate
- `FeedSchedule::query()->where('age_days_start', '<=', $maxBatchAge)` for requirements

**Implementation Status:**
- ✅ **READY** - Product.quantity_on_hand, Product.reorder_level exist
- ⚠️ **PARTIAL** - DailyLog tracks feed consumption but not linked to specific products
- ❌ **MISSING** - No avg_daily_consumption calculation
- ❌ **MISSING** - No chart component for inventory visualization

---

### Zone D: Horizon (Planning & Budget)

**Goal:** "Can I afford the next cycle?"

**Proposed Widgets:**

1. **Feeding Program Budget Projector:**
   - Smart widget analyzing Planned batches
   - Logic: "Next Batch (1,000 birds) starts in 10 days. Feed needed:"
     - 30 bags Starter @ P250 = P7,500
     - 60 bags Grower @ P240 = P14,400
     - 40 bags Finisher @ P235 = P9,400
     - **Total: P31,300** by Oct 15th
   - Data from FeedSchedule + Product prices

2. **Planned Batch Timeline:**
   - Gantt-style or list view
   - Shows: Batch name, start date, chick count, house prep deadline
   - Color-coded: Green (ready), Yellow (prep needed), Red (overdue prep)

**Data Sources:**
- `Batch::query()->where('status', 'Planned')->with('supplier')`
- `FeedSchedule::query()` for feed requirements
- `Product::query()->where('type', 'feed')` for current prices
- Calculate: `total_feed_cost = sum(feed_qty * product.selling_price_cents)`

**Implementation Status:**
- ✅ **READY** - Batch.status = Planned exists
- ✅ **READY** - FeedSchedule model exists with age-based feed requirements
- ⚠️ **PARTIAL** - Product prices exist but no budget projection logic
- ❌ **MISSING** - No Gantt chart or timeline visualization

---

## Architecture Decisions

### Dashboard Structure

**Technology Stack:**
- **Backend:** Laravel controllers return Inertia props
- **Frontend:** React functional components with TypeScript
- **UI Library:** shadcn/ui components (Card, Badge, Table, Chart)
- **Charts:** Recharts (already in package.json dependencies)
- **State:** Inertia shared props + local component state

**File Organization:**
```
resources/js/
├── pages/
│   └── Dashboard.tsx (update existing)
├── components/
│   └── dashboard/
│       ├── CashflowStatsWidget.tsx
│       ├── DebtorWatchlistWidget.tsx
│       ├── ActiveBatchesWidget.tsx (refactor existing BatchCard grid)
│       ├── EnvironmentWidget.tsx
│       ├── LowStockAlertWidget.tsx
│       ├── InventoryChartWidget.tsx
│       ├── FeedBudgetWidget.tsx
│       └── BatchTimelineWidget.tsx
app/Http/Controllers/
└── DashboardController.php (update existing)
```

### Data Flow

```
DashboardController::__invoke()
  ├─> Calculate Cashflow Metrics (Invoices, Payments, LiveSales)
  ├─> Get Active Batches with Latest Logs
  ├─> Calculate Low Stock Alerts (Product.quantity_on_hand vs reorder_level)
  ├─> Project Feed Budget (FeedSchedule × Product prices × Planned batches)
  ├─> Get Planned Batch Timeline
  └─> Return Inertia::render('Dashboard', [...])
```

### Performance Considerations

**Caching Strategy:**
- Cache feed budget projections for 1 hour (changes infrequently)
- Cache avg daily consumption per product for 6 hours
- Cache outstanding debts for 15 minutes
- Real-time queries: Active batches, today's logs, low stock alerts

**Query Optimization:**
- Use eager loading: `Batch::with('dailyLogs', 'expenses')`
- Use database aggregations: `sum()`, `avg()`, `count()` at DB level
- Index: `invoices.due_date`, `products.quantity_on_hand`, `batches.status`

---

## Implementation Phases

### Phase 1: Data Foundation (4-6 hours)

**Goal:** Build the data aggregation layer in DashboardController

**Tasks:**

1. **Update DashboardController** ([app/Http/Controllers/DashboardController.php](app/Http/Controllers/DashboardController.php:22))
   - Add `calculateCashflowMetrics()` method
     - Stock ready for sale (live birds + inventory value)
     - Revenue collected this month (payments)
     - Outstanding debts (invoices - payments)
   - Add `getDebtorWatchlist()` method
     - Query invoices with outstanding balances
     - Filter: amount > P500 OR days_overdue > 30
     - Sort by days_overdue DESC
   - Add `getLowStockAlerts()` method
     - Query products where `quantity_on_hand <= reorder_level`
     - Calculate days_remaining from avg consumption
   - Add `projectFeedBudget()` method
     - Get planned batches
     - Lookup FeedSchedule for feed requirements
     - Calculate cost from Product prices
   - Add `getPlannedBatchTimeline()` method
     - Query Batch where status = Planned
     - Calculate house prep deadline (start_date - 7 days)

2. **Create Service Classes** (if complex logic)
   - `app/Services/Dashboard/CashflowCalculator.php`
   - `app/Services/Dashboard/InventoryAnalyzer.php`
   - `app/Services/Dashboard/FeedBudgetProjector.php`

**Acceptance Criteria:**
- DashboardController returns all required data props
- No N+1 queries (use debugbar to verify)
- Response time < 500ms

---

### Phase 2: Widget Components (6-8 hours)

**Goal:** Build reusable React dashboard widgets

**Tasks:**

1. **Zone A: Cashflow Widgets**
   - Create `CashflowStatsWidget.tsx`
     - 4 stat cards using existing StatCard component
     - Props: stockValue, monthlySales, revenueCollected, outstandingDebt
     - Color coding: Green for revenue, Red for outstanding debt
   - Create `DebtorWatchlistWidget.tsx`
     - Table with Customer, Amount, Days Overdue, Action columns
     - Use shadcn/ui Table component
     - Color-coded badges for overdue severity
     - "Call" and "Email" action buttons (opens mailto:/tel: links)

2. **Zone B: Operations Widgets**
   - Update `ActiveBatchesWidget.tsx` (refactor existing BatchCard grid)
     - Add mortality color coding to BatchCard
     - Add "Last Logged" timestamp with relative time (e.g., "2 hours ago")
     - Add batch health score calculation
   - Create `EnvironmentWidget.tsx`
     - 3 circular gauges using Recharts RadialBarChart
     - Manual data from latest DailyLog (temperature, humidity, ammonia)
     - Optional: Weather forecast widget (OpenWeatherMap API)

3. **Zone C: Inventory Widgets**
   - Create `LowStockAlertWidget.tsx`
     - List of products below reorder level
     - Traffic light indicators (🔴🟡🟢)
     - "Days remaining" calculation display
     - "Order Now" button (future: links to purchase order creation)
   - Create `InventoryChartWidget.tsx`
     - Bar chart: Current vs Required stock for next 7 days
     - Use Recharts BarChart
     - Stacked bars: Green (adequate) + Red (shortfall)

4. **Zone D: Planning Widgets**
   - Create `FeedBudgetWidget.tsx`
     - Display next planned batch details
     - Breakdown: Starter/Grower/Finisher quantities and costs
     - Total budget with deadline date
     - Alert if budget > available cash (future)
   - Create `BatchTimelineWidget.tsx`
     - List or simple timeline view
     - Columns: Batch Name, Start Date, Chick Count, House Prep Status
     - Color-coded status indicators

**Acceptance Criteria:**
- All 8 widgets render without errors
- Responsive design (mobile-first, tablet, desktop)
- Dark mode support
- Loading states for async data
- Empty states when no data

---

### Phase 3: Dashboard Assembly (2-3 hours)

**Goal:** Integrate widgets into the main dashboard page

**Tasks:**

1. **Update Dashboard.tsx** ([resources/js/pages/Dashboard.tsx](resources/js/pages/Dashboard.tsx:78))
   - Replace current content with 4-zone layout:
     ```tsx
     <div className="dashboard-grid">
       {/* Zone A: Cashflow */}
       <CashflowStatsWidget {...props.cashflow} />
       <DebtorWatchlistWidget debts={props.debtors} />

       {/* Zone B: Operations */}
       <ActiveBatchesWidget batches={props.activeBatches} />
       <EnvironmentWidget environment={props.environment} />

       {/* Zone C: Inventory */}
       <LowStockAlertWidget alerts={props.lowStockAlerts} />
       <InventoryChartWidget inventory={props.inventorySummary} />

       {/* Zone D: Planning */}
       <FeedBudgetWidget budget={props.feedBudget} />
       <BatchTimelineWidget batches={props.plannedBatches} />
     </div>
     ```
   - Use CSS Grid for responsive layout
   - Add section headings for each zone
   - Add "Refresh" button to reload dashboard data

2. **CSS/Tailwind Styling**
   - Grid layout: 4 columns on desktop, 2 on tablet, 1 on mobile
   - Zone visual separation (subtle borders or background colors)
   - Consistent card shadows and spacing
   - Dark mode theme support

**Acceptance Criteria:**
- Dashboard displays all 4 zones clearly
- Mobile responsive (stacks vertically)
- No horizontal scrolling on any screen size
- Consistent with existing app design (shadcn/ui theme)

---

## Testing Strategy

### Unit Tests

1. **DashboardController Tests**
   - Test `calculateCashflowMetrics()` with sample data
   - Test `getLowStockAlerts()` filters correctly
   - Test `projectFeedBudget()` calculations accurate
   - Test team scoping (user only sees own team's data)

2. **Service Class Tests** (if created)
   - Test CashflowCalculator formulas
   - Test FeedBudgetProjector with FeedSchedule data
   - Test edge cases (no batches, no stock, etc.)

### Feature Tests

1. **Dashboard Data Integration Test**
   - Create test batch, products, sales
   - Call DashboardController
   - Assert all expected data props returned

2. **Widget Rendering Test (Pest Browser)**
   - Visit `/dashboard`
   - Assert all 4 zones visible
   - Assert stat cards show correct values
   - Assert low stock alerts appear for products below reorder level

### Performance Tests

1. **Load Test**
   - Dashboard should load in < 500ms with 20 active batches
   - No more than 15 database queries
   - Cache hit rate > 70% on subsequent loads

---

## Success Criteria

### Functional Requirements

- [ ] Dashboard displays 4 logical zones (Cashflow, Operations, Inventory, Planning)
- [ ] All 8 widgets render without errors
- [ ] Data is team-scoped (user only sees own farm's data)
- [ ] Mobile-first responsive design
- [ ] Dark mode support
- [ ] No console errors in browser

### Data Requirements

- [ ] Stock valuation calculated from Product inventory
- [ ] Active batches displayed with health metrics
- [ ] Low stock alerts functional (quantity_on_hand vs reorder_level)
- [ ] Feed budget projection for next planned batch
- [ ] Planned batch timeline with prep status

### Performance Requirements

- [ ] Dashboard loads in < 500ms
- [ ] < 15 database queries per page load
- [ ] No N+1 query issues
- [ ] Caching implemented for expensive calculations

### UX Requirements

- [ ] "Morning Coffee Test" - farmer can answer all 5 key questions in < 30 seconds
- [ ] Color-coded alerts catch attention immediately
- [ ] One-click actions (Call debtor, Order stock, Log batch)
- [ ] No jargon - plain language labels

---

## Risks & Mitigations

### Risk 1: Missing Invoice/Payment Data

**Impact:** Cannot show accurate cashflow metrics
**Probability:** HIGH
**Mitigation:**
- Use LiveSaleRecord as proxy for revenue
- Show "Sales Revenue" instead of "Revenue Collected"
- Add placeholder "Outstanding Invoices - Coming Soon in Phase 4"

### Risk 2: Feed Consumption Not Tracked to Stock

**Impact:** Cannot calculate accurate inventory burn rate
**Probability:** MEDIUM (gap already identified)
**Mitigation:**
- Implement `createFeedStockMovement()` in RecordDailyLogAction
- Add `product_id` to DailyLog (optional field for feed product)
- Test with sample batches to verify stock deduction works

### Risk 3: Performance Degradation with Many Batches

**Impact:** Dashboard slow with 50+ historical batches
**Probability:** MEDIUM
**Mitigation:**
- Filter to only Active/Planned batches (not Closed)
- Add database indexes on `batches.status`, `batches.team_id`
- Implement Redis caching for dashboard data
- Paginate historical data in separate "Reports" section

### Risk 4: Scope Creep into Phase 4 Features

**Impact:** Timeline extends beyond dashboard MVP
**Probability:** HIGH
**Mitigation:**
- Strict adherence to plan: only build what data currently supports
- Use "Coming Soon" placeholders for Phase 4 features
- Time-box each phase (no more than allocated hours)

---

## Timeline Estimate

**Total: 12-17 hours (2-3 days)**

| Phase | Tasks | Hours |
|-------|-------|-------|
| Phase 1: Data Foundation | DashboardController + Services | 4-6 |
| Phase 2: Widget Components | 8 React widgets | 6-8 |
| Phase 3: Dashboard Assembly | Layout + styling | 2-3 |
| **Total** | | **12-17** |

---

## Prerequisites

Before implementing this dashboard, we need to complete **Phase 3D: Missing CRUD Pages** to ensure data is properly accessible:

### Critical Blockers

1. **Customer Management Pages** - Required for Debtor Watchlist
   - `resources/js/pages/Customers/Index.tsx`
   - `resources/js/pages/Customers/Show.tsx`
   - Full CRUD controller methods

2. **Stock Movement Pages** - Required for inventory burn rate calculations
   - `resources/js/pages/StockMovements/Index.tsx`
   - Controller with index/show methods

3. **Slaughter/Portioning History** - Required for stock valuation accuracy
   - `resources/js/pages/Slaughter/Index.tsx`
   - `resources/js/pages/Portioning/Index.tsx`

4. **Feed Consumption Integration** - Required for "days remaining" calculations
   - Implement `RecordDailyLogAction::createFeedStockMovement()`
   - Link DailyLog to Product via `product_id`

### Recommended Sequence

1. **First:** Complete Phase 3D CRUD pages (8-12 hours)
2. **Then:** Implement this dashboard plan (12-17 hours)
3. **Result:** Fully functional "Morning Coffee Dashboard" with all data accessible

---

## Appendix: Data Availability Matrix

| Widget | Data Source | Status | Workaround if Blocked |
|--------|-------------|--------|----------------------|
| Stock Ready for Sale | Product.quantity_on_hand × selling_price | ✅ Ready | N/A |
| Stock Sold (Month) | LiveSaleRecord.total_amount | ✅ Ready | N/A |
| Revenue Collected | Payment.amount (Phase 4) | ❌ Blocked | Use LiveSaleRecord instead |
| Outstanding Debt | Invoice - Payments (Phase 4) | ❌ Blocked | Show "Coming Soon" placeholder |
| Debtor Watchlist | Invoice.customer (Phase 4) | ❌ Blocked | Show "Coming Soon" placeholder |
| Active Batches | Batch + DailyLog | ✅ Ready | N/A |
| Environment Gauges | DailyLog.temperature/humidity | ✅ Ready | N/A |
| Low Stock Alerts | Product.quantity_on_hand vs reorder_level | ✅ Ready | N/A |
| Inventory Chart | Product + DailyLog avg consumption | ⚠️ Partial | Manual consumption input |
| Feed Budget | FeedSchedule + Product prices + Batch | ✅ Ready | N/A |
| Batch Timeline | Batch where status=Planned | ✅ Ready | N/A |

**Legend:**
- ✅ Ready - Data exists and can be queried
- ⚠️ Partial - Data exists but calculations missing
- ❌ Blocked - Requires Phase 4 implementation

---

## Critical Files to Modify (When Implementing)

**Backend:**
- `app/Http/Controllers/DashboardController.php` - Add new data methods
- `Domains/Broiler/Actions/RecordDailyLogAction.php` - Implement feed stock movement (line 34)
- `app/Services/Dashboard/` (new directory) - Service classes for calculations

**Frontend:**
- `resources/js/pages/Dashboard.tsx` - Replace with 4-zone layout
- `resources/js/components/dashboard/` (new directory) - 8 widget components

**Database:**
- Add indexes: `batches.status`, `products.quantity_on_hand`, `invoices.due_date`
- Consider caching table for dashboard metrics

---

**Plan Version:** 1.0
**Created:** 2025-12-10
**Author:** Claude (Sonnet 4.5)
**Status:** Deferred - Waiting for Phase 3D CRUD pages completion
**Next Action:** Implement Phase 3D missing CRUD pages first
