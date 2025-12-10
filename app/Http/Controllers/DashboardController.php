<?php

namespace App\Http\Controllers;

use Domains\Broiler\Enums\BatchStatus;
use Domains\Broiler\Models\Batch;
use Domains\Broiler\Models\DailyLog;
use Domains\Broiler\Models\FeedSchedule;
use Domains\Broiler\Models\LiveSaleRecord;
use Domains\Broiler\Services\BatchCalculationService;
use Domains\Finance\Models\Expense;
use Domains\Inventory\Enums\ProductType;
use Domains\Inventory\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        protected BatchCalculationService $calculationService
    ) {}

    /**
     * Display the dashboard with stats and recent batches.
     */
    public function __invoke(): Response
    {
        $teamId = Auth::user()->current_team_id;

        // Get active batches
        $activeBatches = Batch::query()
            ->where('team_id', $teamId)
            ->whereIn('status', [BatchStatus::Active, BatchStatus::Harvesting])
            ->get();

        // Calculate stats
        $totalBirds = $activeBatches->sum('current_quantity');

        $fcrValues = $activeBatches
            ->map(fn (Batch $batch) => $this->calculationService->calculateFCR($batch))
            ->filter()
            ->values();

        $avgFCR = $fcrValues->isNotEmpty() ? $fcrValues->avg() : null;

        $mortalityRates = $activeBatches
            ->map(fn (Batch $batch) => $this->calculationService->calculateMortalityRate($batch));

        $avgMortalityRate = $mortalityRates->isNotEmpty() ? $mortalityRates->avg() : 0;

        // Get today's logs count
        $todayLogs = DailyLog::query()
            ->whereHas('batch', fn ($q) => $q->where('team_id', $teamId))
            ->whereDate('log_date', today())
            ->count();

        // Get all active batches with details for display
        $recentBatches = $activeBatches
            ->sortByDesc('start_date')
            ->map(fn (Batch $batch) => [
                'id' => $batch->id,
                'name' => $batch->name,
                'age_in_days' => $batch->age_in_days,
                'current_bird_count' => $batch->current_quantity,
                'status' => $batch->status->value,
                'statusLabel' => $batch->status->label(),
                'statusColor' => $batch->status->color(),
                'fcr' => $this->calculationService->calculateFCR($batch) ?: null,
                'mortality_rate' => $this->calculationService->calculateMortalityRate($batch),
            ])
            ->values();

        // Pending alerts - batches with high mortality (>5%)
        $pendingAlerts = $activeBatches
            ->filter(fn (Batch $batch) => $this->calculationService->calculateMortalityRate($batch) > 5)
            ->count();

        return Inertia::render('Dashboard', [
            'stats' => [
                'activeBatches' => $activeBatches->count(),
                'totalBirds' => $totalBirds,
                'avgFCR' => $avgFCR,
                'avgMortalityRate' => $avgMortalityRate,
                'todayLogs' => $todayLogs,
                'pendingAlerts' => $pendingAlerts,
            ],
            'recentBatches' => $recentBatches,
            'cashflow' => $this->calculateCashflowMetrics($teamId),
            'cashflowHistory' => $this->getCashflowHistory($teamId, 7),
            'lowStockAlerts' => $this->getLowStockAlerts($teamId),
            'plannedBatches' => $this->getPlannedBatchTimeline($teamId),
        ]);
    }

    /**
     * Calculate cashflow metrics using processed products (carcass, cuts, offal).
     */
    protected function calculateCashflowMetrics(int $teamId): array
    {
        // Calculate stock value based on processed products (not live birds)
        $processedProducts = Product::query()
            ->where('team_id', $teamId)
            ->whereIn('type', [
                ProductType::WholeChicken,
                ProductType::ChickenPieces,
                ProductType::Offal,
            ])
            ->where('is_active', true)
            ->get();

        // Calculate total stock value (quantity × selling price)
        $stockValue = $processedProducts->sum(function ($product) {
            return $product->quantity_on_hand * ($product->selling_price_cents ?? 0);
        });

        // Calculate this month's sales revenue from live sales
        $monthlySales = LiveSaleRecord::query()
            ->where('team_id', $teamId)
            ->whereYear('sale_date', now()->year)
            ->whereMonth('sale_date', now()->month)
            ->sum('total_amount_cents');

        // Get average carcass price (from WholeChicken products)
        $carcassProduct = $processedProducts->firstWhere('type', ProductType::WholeChicken);

        return [
            'stockValue' => $stockValue,
            'monthlySales' => $monthlySales,
            'carcassPrice' => $carcassProduct?->selling_price_cents,
            'processedProducts' => $processedProducts->map(fn ($p) => [
                'name' => $p->name,
                'type' => $p->type->label(),
                'quantity' => $p->quantity_on_hand,
                'value' => $p->quantity_on_hand * ($p->selling_price_cents ?? 0),
            ])->toArray(),
        ];
    }

    /**
     * Get cashflow history for the last N days (cash in vs cash out).
     */
    protected function getCashflowHistory(int $teamId, int $days = 7): array
    {
        $startDate = now()->subDays($days - 1)->startOfDay();
        $endDate = now()->endOfDay();

        // Get cash IN (sales revenue) grouped by date
        $cashIn = LiveSaleRecord::query()
            ->where('team_id', $teamId)
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(sale_date) as date'),
                DB::raw('SUM(total_amount_cents) as amount')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Get cash OUT (expenses) grouped by date
        $cashOut = Expense::query()
            ->where('team_id', $teamId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as amount')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Build array for each day
        $history = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dateLabel = now()->subDays($i)->format('M d');

            $history[] = [
                'date' => $date,
                'date_label' => $dateLabel,
                'cash_in' => $cashIn->get($date)?->amount ?? 0,
                'cash_out' => $cashOut->get($date)?->amount ?? 0,
                'net' => ($cashIn->get($date)?->amount ?? 0) - ($cashOut->get($date)?->amount ?? 0),
            ];
        }

        // Calculate totals
        $totalCashIn = collect($history)->sum('cash_in');
        $totalCashOut = collect($history)->sum('cash_out');

        return [
            'daily' => $history,
            'totals' => [
                'cash_in' => $totalCashIn,
                'cash_out' => $totalCashOut,
                'net' => $totalCashIn - $totalCashOut,
            ],
            'period' => [
                'start' => now()->subDays($days - 1)->format('M d'),
                'end' => now()->format('M d'),
                'days' => $days,
            ],
        ];
    }

    /**
     * Get products below reorder level with days remaining estimate.
     */
    protected function getLowStockAlerts(int $teamId): array
    {
        $lowStockProducts = Product::query()
            ->where('team_id', $teamId)
            ->where('is_active', true)
            ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->orderBy('quantity_on_hand')
            ->limit(5)
            ->get();

        return $lowStockProducts->map(function (Product $product) {
            // Calculate average daily consumption from recent logs (simplified)
            $avgDailyConsumption = 1; // Default to 1 to prevent division by zero

            // For feed products, we could calculate from DailyLog.feed_consumed_kg
            // This is a simplified version - production would need proper product linking

            $daysRemaining = $avgDailyConsumption > 0
                ? (int) floor($product->quantity_on_hand / $avgDailyConsumption)
                : null;

            return [
                'id' => $product->id,
                'name' => $product->name,
                'type' => $product->type->value,
                'type_label' => $product->type->label(),
                'quantity_on_hand' => $product->quantity_on_hand,
                'reorder_level' => $product->reorder_level,
                'unit' => $product->unit,
                'days_remaining' => $daysRemaining,
                'is_critical' => $product->quantity_on_hand <= ($product->reorder_level * 0.5),
            ];
        })->toArray();
    }

    /**
     * Get planned batch timeline with feed budget projections.
     */
    protected function getPlannedBatchTimeline(int $teamId): array
    {
        $plannedBatches = Batch::query()
            ->where('team_id', $teamId)
            ->where('status', BatchStatus::Planned)
            ->with('supplier')
            ->orderBy('start_date')
            ->limit(3)
            ->get();

        return $plannedBatches->map(function (Batch $batch) use ($teamId) {
            // Calculate days until start
            $daysUntilStart = now()->diffInDays($batch->start_date, false);

            // Get feed schedules to estimate feed budget
            $feedSchedules = FeedSchedule::query()
                ->where('team_id', $teamId)
                ->where('is_active', true)
                ->orderBy('age_from_days')
                ->get();

            // Simple budget estimate (this is simplified - production would be more detailed)
            $estimatedFeedCost = $feedSchedules->sum(function ($schedule) use ($batch) {
                $days = ($schedule->age_to_days - $schedule->age_from_days) + 1;
                $dailyFeed = $schedule->calculateDailyFeed($batch->initial_quantity);

                return $dailyFeed * $days * 2.5; // Rough estimate at P2.5/kg
            });

            return [
                'id' => $batch->id,
                'name' => $batch->name,
                'batch_number' => $batch->batch_number,
                'start_date' => $batch->start_date->format('M d, Y'),
                'days_until_start' => $daysUntilStart,
                'initial_quantity' => $batch->initial_quantity,
                'supplier_name' => $batch->supplier?->name,
                'estimated_feed_cost' => (int) $estimatedFeedCost,
                'status_color' => $daysUntilStart < 0
                    ? 'red'
                    : ($daysUntilStart <= 7 ? 'yellow' : 'green'),
            ];
        })->toArray();
    }
}
