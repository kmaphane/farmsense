<?php

namespace App\Http\Controllers\Batches;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Domains\Broiler\DTOs\BatchData;
use Domains\Broiler\Enums\BatchStatus;
use Domains\Broiler\Models\Batch;
use Domains\Broiler\Resources\BatchFormDataResource;
use Domains\Broiler\Services\BatchCalculationService;
use Domains\CRM\Models\Customer;
use Domains\CRM\Models\Supplier;
use Domains\Inventory\Enums\ProductType;
use Domains\Inventory\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class BatchController extends Controller
{
    public function __construct(
        protected BatchCalculationService $calculationService
    ) {}

    /**
     * Display a listing of active batches for field mode.
     */
    public function index(): Response
    {
        $batches = Batch::query()
            ->where('team_id', Auth::user()->current_team_id)
            ->whereIn('status', [BatchStatus::Active, BatchStatus::Harvesting])
            ->with(['supplier', 'dailyLogs'])
            ->orderBy('start_date', 'desc')
            ->get()
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
            ]);

        return Inertia::render('Batches/Index', [
            'batches' => $batches,
        ]);
    }

    /**
     * Display the specified batch with full details.
     */
    public function show(Batch $batch): Response
    {
        // Authorization: ensure user can only view their team's batches
        abort_if($batch->team_id !== Auth::user()->current_team_id, 403, 'Unauthorized access to this batch.');

        $batch->load(['supplier', 'dailyLogs' => fn ($q) => $q->orderBy('log_date', 'desc')]);

        $stats = $this->calculationService->getBatchStatistics($batch);

        // Get last log for reference when creating new logs
        $lastLog = $batch->dailyLogs->first();

        // Calculate suggested date (day after last log, or today if no logs)
        $suggestedDate = $lastLog
            ? $lastLog->log_date->addDay()->toDateString()
            : now()->toDateString();

        return Inertia::render('Batches/Show', [
            'batch' => [
                'id' => $batch->id,
                'name' => $batch->name,
                'age_in_days' => $batch->age_in_days,
                'current_bird_count' => $batch->current_quantity,
                'initial_bird_count' => $batch->initial_quantity,
                'status' => $batch->status->value,
                'statusLabel' => $batch->status->label(),
                'statusColor' => $batch->status->color(),
                'start_date' => $batch->start_date->toDateString(),
                'target_weight_kg' => (float) $batch->target_weight_kg,
                'supplier' => $batch->supplier ? ['name' => $batch->supplier->name] : null,
            ],
            'stats' => [
                'fcr' => $stats['fcr'] ?: null,
                'epef' => $stats['epef'] ?: null,
                'mortalityRate' => $stats['mortality_rate'],
                'avgDailyGain' => $stats['average_weight_kg'] > 0 && $batch->age_in_days > 0
                    ? round(($stats['average_weight_kg'] * 1000) / $batch->age_in_days, 1)
                    : null,
                'totalFeedConsumed' => (float) $stats['total_feed_consumed'],
            ],
            'dailyLogs' => $batch->dailyLogs->map(fn ($log) => [
                'id' => $log->id,
                'log_date' => $log->log_date->toDateString(),
                'mortality_count' => $log->mortality_count,
                'feed_consumed_kg' => (float) $log->feed_consumed_kg,
                'water_consumed_liters' => $log->water_consumed_liters ? (float) $log->water_consumed_liters : null,
                'temperature_celsius' => $log->temperature_celsius ? (float) $log->temperature_celsius : null,
                'humidity_percent' => $log->humidity_percent ? (float) $log->humidity_percent : null,
                'ammonia_ppm' => $log->ammonia_ppm ? (float) $log->ammonia_ppm : null,
                'rainfall_mm' => $log->rainfall_mm ? (float) $log->rainfall_mm : null,
                'isEditable' => $log->isEditable(),
            ]),
            'lastLog' => $lastLog ? [
                'log_date' => $lastLog->log_date->toDateString(),
                'mortality_count' => $lastLog->mortality_count,
                'feed_consumed_kg' => (float) $lastLog->feed_consumed_kg,
                'water_consumed_liters' => $lastLog->water_consumed_liters ? (float) $lastLog->water_consumed_liters : null,
                'temperature_celsius' => $lastLog->temperature_celsius ? (float) $lastLog->temperature_celsius : null,
                'humidity_percent' => $lastLog->humidity_percent ? (float) $lastLog->humidity_percent : null,
            ] : null,
            'suggestedDate' => $suggestedDate,
            // Live sale data for sheet
            'liveSale' => $this->getLiveSaleData($batch),
        ]);
    }

    /**
     * Get live sale data for the batch show page sheet.
     *
     * @return array{liveBirdPrice: int|null, customers: array<int, array{id: int, name: string}>}
     */
    protected function getLiveSaleData(Batch $batch): array
    {
        $teamId = Auth::user()->current_team_id;

        // Only provide data if batch can have live sales
        $canSellLive = in_array($batch->status, [BatchStatus::Active, BatchStatus::Harvesting])
            && $batch->current_quantity > 0;

        if (! $canSellLive) {
            return [
                'canSell' => false,
                'liveBirdPrice' => null,
                'customers' => [],
            ];
        }

        // Get live bird product price
        $liveBirdProduct = Product::query()
            ->where('team_id', $teamId)
            ->where('type', ProductType::LiveBird)
            ->where('is_active', true)
            ->first();

        // Get customers for optional linking
        $customers = Customer::query()
            ->where('team_id', $teamId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Customer $customer) => [
                'id' => $customer->id,
                'name' => $customer->name,
            ])
            ->toArray();

        return [
            'canSell' => true,
            'liveBirdPrice' => $liveBirdProduct?->selling_price_cents,
            'customers' => $customers,
        ];
    }

    /**
     * Get form data for Quick Actions sheet (JSON API).
     */
    public function data(): BatchFormDataResource
    {
        $suppliers = Supplier::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Generate suggested batch number
        $year = now()->year;
        $latestBatch = Batch::query()
            ->where('team_id', Auth::user()->current_team_id)
            ->whereYear('created_at', $year)
            ->latest('id')
            ->first();

        $nextNumber = $latestBatch ? ((int) substr($latestBatch->batch_number, -3)) + 1 : 1;
        $suggestedBatchNumber = sprintf('B-%d-%03d', $year, $nextNumber);

        return new BatchFormDataResource([
            'suppliers' => $suppliers,
            'suggestedBatchNumber' => $suggestedBatchNumber,
            'suggestedStartDate' => now()->toDateString(),
        ]);
    }

    /**
     * Store a newly created batch record.
     */
    public function store(BatchData $data): JsonResponse
    {
        $batch = Batch::create([
            'team_id' => Auth::user()->current_team_id,
            'name' => $data->name,
            'batch_number' => $data->batch_number,
            'start_date' => $data->start_date,
            'status' => BatchStatus::Planned,
            'initial_quantity' => $data->initial_quantity,
            'current_quantity' => $data->initial_quantity,
            'supplier_id' => $data->supplier_id,
            'target_weight_kg' => $data->target_weight_kg,
        ]);

        return response()->json([
            'message' => 'Batch created successfully',
            'batch_id' => $batch->id,
        ], 201);
    }
}
