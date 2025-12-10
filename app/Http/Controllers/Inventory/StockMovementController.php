<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Domains\Inventory\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class StockMovementController extends Controller
{
    /**
     * Display a listing of stock movements.
     */
    public function index(): Response
    {
        $teamId = Auth::user()->current_team_id;

        $stockMovements = StockMovement::query()
            ->where('team_id', $teamId)
            ->with(['product', 'warehouse', 'recordedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('StockMovements/Index', [
            'stockMovements' => $stockMovements->through(fn ($movement) => [
                'id' => $movement->id,
                'product_name' => $movement->product->name,
                'warehouse_name' => $movement->warehouse->name,
                'quantity' => $movement->quantity,
                'movement_type' => $movement->movement_type,
                'reason' => $movement->reason,
                'recorded_by' => $movement->recordedBy->name,
                'created_at' => $movement->created_at->format('M d, Y g:i A'),
            ]),
            'pagination' => [
                'current_page' => $stockMovements->currentPage(),
                'last_page' => $stockMovements->lastPage(),
                'per_page' => $stockMovements->perPage(),
                'total' => $stockMovements->total(),
            ],
        ]);
    }

    /**
     * Display the specified stock movement.
     */
    public function show(StockMovement $stockMovement): Response
    {
        // Authorization: ensure user can only view their team's records
        abort_if($stockMovement->team_id !== Auth::user()->current_team_id, 403, 'Unauthorized access to this record.');

        $stockMovement->load(['product', 'warehouse', 'recordedBy']);

        return Inertia::render('StockMovements/Show', [
            'stockMovement' => [
                'id' => $stockMovement->id,
                'product_name' => $stockMovement->product->name,
                'product_id' => $stockMovement->product_id,
                'warehouse_name' => $stockMovement->warehouse->name,
                'warehouse_id' => $stockMovement->warehouse_id,
                'quantity' => $stockMovement->quantity,
                'movement_type' => $stockMovement->movement_type,
                'reason' => $stockMovement->reason,
                'notes' => $stockMovement->notes,
                'recorded_by' => $stockMovement->recordedBy->name,
                'created_at' => $stockMovement->created_at->format('M d, Y g:i A'),
            ],
        ]);
    }
}
