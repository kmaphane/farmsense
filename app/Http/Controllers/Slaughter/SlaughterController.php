<?php

namespace App\Http\Controllers\Slaughter;

use App\Http\Controllers\Controller;
use Domains\Broiler\Actions\RecordSlaughterAction;
use Domains\Broiler\DTOs\SlaughterData;
use Domains\Broiler\Enums\BatchStatus;
use Domains\Broiler\Enums\DiscrepancyReason;
use Domains\Broiler\Models\Batch;
use Domains\Broiler\Models\SlaughterRecord;
use Domains\Inventory\Enums\ProductType;
use Domains\Inventory\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class SlaughterController extends Controller
{
    public function __construct(
        protected RecordSlaughterAction $recordSlaughterAction
    ) {}

    /**
     * Show the form for creating a new slaughter record.
     */
    public function create(): Response
    {
        $teamId = Auth::user()->current_team_id;

        // Get active batches with birds available for slaughter
        $batches = Batch::query()
            ->where('team_id', $teamId)
            ->whereIn('status', [BatchStatus::Active, BatchStatus::Harvesting])
            ->where('current_quantity', '>', 0)
            ->orderBy('name')
            ->get()
            ->map(fn (Batch $batch) => [
                'id' => $batch->id,
                'name' => $batch->name,
                'batch_number' => $batch->batch_number,
                'current_quantity' => $batch->current_quantity,
                'age_in_days' => $batch->age_in_days,
            ]);

        // Get poultry products with yield data for client-side calculation
        $products = Product::query()
            ->where('team_id', $teamId)
            ->whereIn('type', [
                ProductType::WholeChicken,
                ProductType::Offal,
                ProductType::ByProduct,
            ])
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'type' => $product->type->value,
                'yield_per_bird' => (float) ($product->yield_per_bird ?? 0),
                'units_per_package' => (float) ($product->units_per_package ?? 1),
                'package_unit' => $product->package_unit?->value,
            ]);

        // Get discrepancy reasons for dropdown
        $discrepancyReasons = collect(DiscrepancyReason::cases())
            ->map(fn (DiscrepancyReason $reason) => [
                'value' => $reason->value,
                'label' => $reason->label(),
            ]);

        return Inertia::render('Slaughter/Create', [
            'batches' => $batches,
            'products' => $products,
            'discrepancyReasons' => $discrepancyReasons,
            'suggestedDate' => now()->toDateString(),
        ]);
    }

    /**
     * Store a newly created slaughter record.
     */
    public function store(SlaughterData $data): RedirectResponse
    {
        $slaughterRecord = $this->recordSlaughterAction->execute(
            teamId: $data->team_id,
            slaughterDate: $data->slaughter_date,
            batchSources: $data->toBatchSourcesArray(),
            yields: $data->toYieldsArray(),
            recordedBy: Auth::id(),
            notes: $data->notes,
        );

        return to_route('slaughter.show', $slaughterRecord)
            ->with('success', "Slaughter record #{$slaughterRecord->id} created successfully. {$slaughterRecord->total_birds_processed} birds processed.");
    }

    /**
     * Display the specified slaughter record.
     */
    public function show(SlaughterRecord $record): Response
    {
        // Authorization: ensure user can only view their team's records
        abort_if($record->team_id !== Auth::user()->current_team_id, 403, 'Unauthorized access to this record.');

        $record->load(['batchSources.batch', 'yields.product', 'recorder']);

        return Inertia::render('Slaughter/Show', [
            'record' => [
                'id' => $record->id,
                'slaughter_date' => $record->slaughter_date->toDateString(),
                'total_birds_processed' => $record->total_birds_processed,
                'notes' => $record->notes,
                'recorded_by' => $record->recorder?->name,
                'created_at' => $record->created_at->toDateTimeString(),
            ],
            'batchSources' => $record->batchSources->map(fn ($source) => [
                'id' => $source->id,
                'batch_name' => $source->batch->name,
                'batch_number' => $source->batch->batch_number,
                'expected_quantity' => $source->expected_quantity,
                'actual_quantity' => $source->actual_quantity,
                'discrepancy_reason' => $source->discrepancy_reason?->label(),
                'discrepancy_notes' => $source->discrepancy_notes,
                'has_discrepancy' => $source->expected_quantity > $source->actual_quantity,
            ]),
            'yields' => $record->yields->map(fn ($yield) => [
                'id' => $yield->id,
                'product_name' => $yield->product->name,
                'estimated_quantity' => $yield->estimated_quantity,
                'actual_quantity' => $yield->actual_quantity,
                'household_consumed' => $yield->household_consumed,
            ]),
        ]);
    }
}
