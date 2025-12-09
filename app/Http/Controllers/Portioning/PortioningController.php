<?php

namespace App\Http\Controllers\Portioning;

use App\Http\Controllers\Controller;
use Domains\Broiler\Actions\RecordPortioningAction;
use Domains\Broiler\DTOs\PortioningData;
use Domains\Broiler\Resources\PortioningFormDataResource;
use Domains\Inventory\Enums\ProductType;
use Domains\Inventory\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class PortioningController extends Controller
{
    public function __construct(
        protected RecordPortioningAction $recordPortioningAction
    ) {}

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
        $this->recordPortioningAction->execute($data);

        return redirect()
            ->route('batches.index')
            ->with('success', 'Portioning record created successfully.');
    }
}
