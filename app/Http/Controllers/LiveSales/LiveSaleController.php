<?php

namespace App\Http\Controllers\LiveSales;

use App\Http\Controllers\Controller;
use Domains\Broiler\Actions\RecordLiveSaleAction;
use Domains\Broiler\DTOs\LiveSaleData;
use Domains\Broiler\Enums\BatchStatus;
use Domains\Broiler\Models\Batch;
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
