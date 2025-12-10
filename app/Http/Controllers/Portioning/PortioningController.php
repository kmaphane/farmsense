<?php

namespace App\Http\Controllers\Portioning;

use App\Http\Controllers\Controller;
use Domains\Broiler\Actions\RecordPortioningAction;
use Domains\Broiler\DTOs\PortioningData;
use Domains\Broiler\Models\PortioningRecord;
use Domains\Broiler\Resources\PortioningFormDataResource;
use Domains\Inventory\Enums\ProductType;
use Domains\Inventory\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PortioningController extends Controller
{
    public function __construct(
        protected RecordPortioningAction $recordPortioningAction
    ) {}

    /**
     * Display a listing of portioning records.
     */
    public function index(): Response
    {
        $teamId = Auth::user()->current_team_id;

        $portioningRecords = PortioningRecord::query()
            ->where('team_id', $teamId)
            ->with('recorder')
            ->orderBy('portioning_date', 'desc')
            ->paginate(15);

        return Inertia::render('Portioning/Index', [
            'portioningRecords' => $portioningRecords->through(fn ($record) => [
                'id' => $record->id,
                'portioning_date' => $record->portioning_date->toDateString(),
                'portioning_date_formatted' => $record->portioning_date->format('M d, Y'),
                'whole_birds_used' => $record->whole_birds_used,
                'packs_produced' => $record->packs_produced,
                'pack_weight_kg' => (float) $record->pack_weight_kg,
                'total_weight' => round($record->packs_produced * $record->pack_weight_kg, 2),
                'recorded_by' => $record->recorder->name,
            ]),
            'pagination' => [
                'current_page' => $portioningRecords->currentPage(),
                'last_page' => $portioningRecords->lastPage(),
                'per_page' => $portioningRecords->perPage(),
                'total' => $portioningRecords->total(),
            ],
        ]);
    }

    /**
     * Display the specified portioning record.
     */
    public function show(PortioningRecord $portioning): Response
    {
        // Authorization
        abort_if($portioning->team_id !== Auth::user()->current_team_id, 403);

        $portioning->load('recorder');

        return Inertia::render('Portioning/Show', [
            'portioningRecord' => [
                'id' => $portioning->id,
                'portioning_date' => $portioning->portioning_date->toDateString(),
                'portioning_date_formatted' => $portioning->portioning_date->format('F d, Y'),
                'whole_birds_used' => $portioning->whole_birds_used,
                'packs_produced' => $portioning->packs_produced,
                'pack_weight_kg' => (float) $portioning->pack_weight_kg,
                'total_weight' => round($portioning->packs_produced * $portioning->pack_weight_kg, 2),
                'notes' => $portioning->notes,
                'recorded_by' => $portioning->recorder->name,
                'created_at' => $portioning->created_at->format('M d, Y g:i A'),
            ],
        ]);
    }

    /**
     * Get form data for Quick Actions sheet (JSON API).
     */
    public function data(): PortioningFormDataResource
    {
        $teamId = Auth::user()->current_team_id;

        // Get whole chicken product with current stock
        $wholeChickenStock = Product::query()
            ->where('team_id', $teamId)
            ->where('type', ProductType::WholeChicken)
            ->where('is_active', true)
            ->first();

        // Get chicken pieces product
        $chickenPiecesProduct = Product::query()
            ->where('team_id', $teamId)
            ->where('type', ProductType::ChickenPieces)
            ->where('is_active', true)
            ->first();

        return new PortioningFormDataResource([
            'wholeChickenStock' => $wholeChickenStock,
            'chickenPiecesProduct' => $chickenPiecesProduct,
            'suggestedDate' => now()->toDateString(),
            'defaultPackWeight' => 1.2,
        ]);
    }

    /**
     * Store a newly created portioning record.
     */
    public function store(PortioningData $data): RedirectResponse
    {
        $portioningRecord = $this->recordPortioningAction->execute(
            teamId: $data->team_id,
            portioningDate: $data->portioning_date,
            wholeBirdsUsed: $data->whole_birds_used,
            packsProduced: $data->packs_produced,
            packWeightKg: $data->pack_weight_kg ?? 0.5,
            recordedBy: Auth::id(),
            notes: $data->notes
        );

        return redirect()
            ->route('portioning.show', $portioningRecord)
            ->with('success', 'Portioning record created successfully.');
    }
}
