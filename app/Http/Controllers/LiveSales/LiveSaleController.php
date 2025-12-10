<?php

namespace App\Http\Controllers\LiveSales;

use App\Http\Controllers\Controller;
use Domains\Broiler\Actions\RecordLiveSaleAction;
use Domains\Broiler\DTOs\LiveSaleData;
use Domains\Broiler\Enums\BatchStatus;
use Domains\Broiler\Models\Batch;
use Domains\Broiler\Models\LiveSaleRecord;
use Domains\CRM\Models\Customer;
use Domains\Inventory\Enums\ProductType;
use Domains\Inventory\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LiveSaleController extends Controller
{
    public function __construct(
        protected RecordLiveSaleAction $recordLiveSaleAction
    ) {}

    /**
     * Display a listing of live sale records.
     */
    public function index(): Response
    {
        $teamId = Auth::user()->current_team_id;

        $liveSaleRecords = LiveSaleRecord::query()
            ->where('team_id', $teamId)
            ->with(['batch', 'customer', 'recorder'])
            ->orderBy('sale_date', 'desc')
            ->paginate(15);

        return Inertia::render('LiveSales/Index', [
            'liveSaleRecords' => $liveSaleRecords->through(fn ($record) => [
                'id' => $record->id,
                'sale_date' => $record->sale_date->toDateString(),
                'sale_date_formatted' => $record->sale_date->format('M d, Y'),
                'batch_name' => $record->batch->name,
                'quantity_sold' => $record->quantity_sold,
                'unit_price' => $record->unit_price,
                'total_amount' => $record->total_amount,
                'customer_name' => $record->customer?->name ?? 'Walk-in Customer',
                'recorded_by' => $record->recorder->name,
            ]),
            'pagination' => [
                'current_page' => $liveSaleRecords->currentPage(),
                'last_page' => $liveSaleRecords->lastPage(),
                'per_page' => $liveSaleRecords->perPage(),
                'total' => $liveSaleRecords->total(),
            ],
        ]);
    }

    /**
     * Display the specified live sale record.
     */
    public function show(LiveSaleRecord $liveSale): Response
    {
        // Authorization: ensure user can only view their team's records
        abort_if($liveSale->team_id !== Auth::user()->current_team_id, 403, 'Unauthorized access to this record.');

        $liveSale->load(['batch', 'customer', 'recorder']);

        return Inertia::render('LiveSales/Show', [
            'liveSaleRecord' => [
                'id' => $liveSale->id,
                'sale_date' => $liveSale->sale_date->toDateString(),
                'sale_date_formatted' => $liveSale->sale_date->format('M d, Y'),
                'batch_name' => $liveSale->batch->name,
                'batch_id' => $liveSale->batch_id,
                'quantity_sold' => $liveSale->quantity_sold,
                'unit_price' => $liveSale->unit_price,
                'total_amount' => $liveSale->total_amount,
                'customer_name' => $liveSale->customer?->name ?? 'Walk-in Customer',
                'customer_id' => $liveSale->customer_id,
                'notes' => $liveSale->notes,
                'recorded_by' => $liveSale->recorder->name,
                'created_at' => $liveSale->created_at->format('M d, Y g:i A'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new live sale.
     */
    public function create(Batch $batch): Response
    {
        $teamId = Auth::user()->current_team_id;

        // Authorization: ensure user can only access their team's batches
        abort_if($batch->team_id !== $teamId, 403, 'Unauthorized access to this batch.');

        // Validate batch can have live sales
        abort_if(
            ! in_array($batch->status, [BatchStatus::Active, BatchStatus::Harvesting]),
            422,
            'Cannot sell live birds from a batch that is not active or harvesting.'
        );

        abort_if($batch->current_quantity <= 0, 422, 'No birds available in this batch.');

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

        return Inertia::render('LiveSales/Create', [
            'batch' => [
                'id' => $batch->id,
                'name' => $batch->name,
                'current_quantity' => $batch->current_quantity,
                'age_in_days' => $batch->age_in_days,
            ],
            'liveBirdPrice' => $liveBirdProduct?->selling_price_cents,
            'customers' => $customers,
        ]);
    }

    /**
     * Store a newly created live sale.
     */
    public function store(LiveSaleData $data): RedirectResponse
    {
        $liveSale = $this->recordLiveSaleAction->execute($data);

        return redirect()
            ->route('batches.show', $liveSale->batch_id)
            ->with('success', 'Live sale recorded successfully.');
    }
}
