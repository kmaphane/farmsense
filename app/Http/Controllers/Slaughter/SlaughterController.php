<?php

namespace App\Http\Controllers\Slaughter;

use App\Http\Controllers\Controller;
use Domains\Broiler\Actions\CalculateSlaughterYieldsAction;
use Domains\Broiler\Actions\RecordSlaughterAction;
use Domains\Broiler\DTOs\SlaughterData;
use Domains\Broiler\Enums\BatchStatus;
use Domains\Broiler\Models\Batch;
use Domains\Broiler\Models\SlaughterRecord;
use Domains\Broiler\Resources\SlaughterFormDataResource;
use Domains\Inventory\Enums\ProductType;
use Domains\Inventory\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class SlaughterController extends Controller
{
    public function __construct(
        protected RecordSlaughterAction $recordSlaughterAction,
        protected CalculateSlaughterYieldsAction $calculateYieldsAction
    ) {}

    /**
     * Display a listing of slaughter records.
     */
    public function index(): Response
    {
        $teamId = Auth::user()->current_team_id;

        $slaughterRecords = SlaughterRecord::query()
            ->where('team_id', $teamId)
            ->with(['batchSources.batch', 'recorder'])
            ->withCount('yields')
            ->orderBy('slaughter_date', 'desc')
            ->paginate(15);

        return Inertia::render('Slaughter/Index', [
            'slaughterRecords' => $slaughterRecords->through(fn ($record) => [
                'id' => $record->id,
                'slaughter_date' => $record->slaughter_date->toDateString(),
                'slaughter_date_formatted' => $record->slaughter_date->format('M d, Y'),
                'total_birds_slaughtered' => $record->total_birds_slaughtered,
                'batches_count' => $record->batchSources->count(),
                'batches_names' => $record->batchSources->pluck('batch.name')->implode(', '),
                'yields_count' => $record->yields_count,
                'has_discrepancies' => $record->batchSources->contains(fn ($source) => $source->actual_quantity < $source->expected_quantity),
                'recorded_by' => $record->recorder->name,
            ]),
            'pagination' => [
                'current_page' => $slaughterRecords->currentPage(),
                'last_page' => $slaughterRecords->lastPage(),
                'per_page' => $slaughterRecords->perPage(),
                'total' => $slaughterRecords->total(),
            ],
        ]);
    }

    /**
     * Get form data for Quick Actions sheet (JSON API).
     */
    public function data(): SlaughterFormDataResource
    {
        $teamId = Auth::user()->current_team_id;

        // Get active batches that can be slaughtered
        $batches = Batch::query()
            ->where('team_id', $teamId)
            ->whereIn('status', [BatchStatus::Active, BatchStatus::Harvesting])
            ->where('current_quantity', '>', 0)
            ->orderBy('start_date', 'desc')
            ->get();

        // Get poultry products with yield information
        $products = Product::query()
            ->where('team_id', $teamId)
            ->whereIn('type', [
                ProductType::WholeChicken,
                ProductType::ChickenPieces,
                ProductType::Offal,
            ])
            ->where('is_active', true)
            ->whereNotNull('yield_per_bird')
            ->orderBy('name')
            ->get();

        $discrepancyReasons = [
            ['value' => 'mortality_after_pickup', 'label' => 'Mortality after pickup'],
            ['value' => 'counting_error', 'label' => 'Counting error'],
            ['value' => 'record_error', 'label' => 'Record keeping error'],
            ['value' => 'other', 'label' => 'Other'],
        ];

        return new SlaughterFormDataResource([
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
        $slaughterRecord = $this->recordSlaughterAction->execute($data);

        return redirect()
            ->route('slaughter.show', $slaughterRecord)
            ->with('success', 'Slaughter record created successfully.');
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
            'slaughterRecord' => [
                'id' => $record->id,
                'slaughter_date' => $record->slaughter_date->toDateString(),
                'total_birds_slaughtered' => $record->total_birds_slaughtered,
                'total_live_weight_kg' => $record->total_live_weight_kg ? (float) $record->total_live_weight_kg : null,
                'total_dressed_weight_kg' => $record->total_dressed_weight_kg ? (float) $record->total_dressed_weight_kg : null,
                'household_consumption_notes' => $record->household_consumption_notes,
                'notes' => $record->notes,
                'recorded_by' => $record->recorder->name,
            ],
            'batchSources' => $record->batchSources->map(fn ($source) => [
                'batch_name' => $source->batch->name,
                'expected_quantity' => $source->expected_quantity,
                'actual_quantity' => $source->actual_quantity,
                'discrepancy_reason' => $source->discrepancy_reason?->value,
                'discrepancy_notes' => $source->discrepancy_notes,
                'has_discrepancy' => $source->actual_quantity < $source->expected_quantity,
            ])->toArray(),
            'yields' => $record->yields->map(fn ($yield) => [
                'product_name' => $yield->product->name,
                'estimated_quantity' => $yield->estimated_quantity,
                'actual_quantity' => $yield->actual_quantity,
                'household_consumed' => $yield->household_consumed,
            ])->toArray(),
        ]);
    }
}
