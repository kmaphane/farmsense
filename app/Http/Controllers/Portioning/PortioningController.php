<?php

namespace App\Http\Controllers\Portioning;

use App\Http\Controllers\Controller;
use Domains\Broiler\Actions\RecordPortioningAction;
use Domains\Broiler\DTOs\PortioningData;
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
     * Show the form for creating a new portioning record.
     */
    public function create(): Response
    {
        $teamId = Auth::user()->current_team_id;

        // Get whole chicken stock level
        $wholeChickenProduct = Product::query()
            ->where('team_id', $teamId)
            ->where('type', ProductType::WholeChicken)
            ->where('is_active', true)
            ->first();

        // Get chicken pieces product for reference
        $chickenPiecesProduct = Product::query()
            ->where('team_id', $teamId)
            ->where('type', ProductType::ChickenPieces)
            ->where('is_active', true)
            ->first();

        return Inertia::render('Portioning/Create', [
            'wholeChickenStock' => $wholeChickenProduct ? [
                'id' => $wholeChickenProduct->id,
                'name' => $wholeChickenProduct->name,
                'quantity_on_hand' => $wholeChickenProduct->quantity_on_hand ?? 0,
            ] : null,
            'chickenPiecesProduct' => $chickenPiecesProduct ? [
                'id' => $chickenPiecesProduct->id,
                'name' => $chickenPiecesProduct->name,
                'quantity_on_hand' => $chickenPiecesProduct->quantity_on_hand ?? 0,
                'units_per_package' => (float) ($chickenPiecesProduct->units_per_package ?? 0.5),
                'package_unit' => $chickenPiecesProduct->package_unit?->value,
            ] : null,
            'suggestedDate' => now()->toDateString(),
            'defaultPackWeight' => 0.5, // 0.5kg packs
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
            packWeightKg: $data->pack_weight_kg,
            recordedBy: Auth::id(),
            notes: $data->notes,
        );

        return to_route('batches.index')
            ->with('success', "Portioning record created. {$data->whole_birds_used} whole birds converted to {$data->packs_produced} packs.");
    }
}
