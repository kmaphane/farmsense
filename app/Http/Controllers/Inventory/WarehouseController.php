<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Domains\Inventory\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class WarehouseController extends Controller
{
    /**
     * Display a listing of warehouses.
     */
    public function index(): Response
    {
        $teamId = Auth::user()->current_team_id;

        $warehouses = Warehouse::query()
            ->where('team_id', $teamId)
            ->withCount('stockMovements')
            ->orderBy('name')
            ->paginate(15);

        return Inertia::render('Warehouses/Index', [
            'warehouses' => $warehouses->through(fn ($warehouse) => [
                'id' => $warehouse->id,
                'name' => $warehouse->name,
                'location' => $warehouse->location,
                'capacity' => $warehouse->capacity,
                'is_active' => $warehouse->is_active,
                'stock_movements_count' => $warehouse->stock_movements_count,
            ]),
            'pagination' => [
                'current_page' => $warehouses->currentPage(),
                'last_page' => $warehouses->lastPage(),
                'per_page' => $warehouses->perPage(),
                'total' => $warehouses->total(),
            ],
        ]);
    }

    /**
     * Display the specified warehouse.
     */
    public function show(Warehouse $warehouse): Response
    {
        // Authorization: ensure user can only view their team's records
        abort_if($warehouse->team_id !== Auth::user()->current_team_id, 403, 'Unauthorized access to this record.');

        $warehouse->loadCount('stockMovements');

        // Get recent stock movements for this warehouse
        $recentMovements = $warehouse->stockMovements()
            ->with(['product', 'recordedBy'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn ($movement) => [
                'id' => $movement->id,
                'product_name' => $movement->product->name,
                'quantity' => $movement->quantity,
                'movement_type' => $movement->movement_type,
                'reason' => $movement->reason,
                'recorded_by' => $movement->recordedBy->name,
                'created_at' => $movement->created_at->format('M d, Y g:i A'),
            ]);

        return Inertia::render('Warehouses/Show', [
            'warehouse' => [
                'id' => $warehouse->id,
                'name' => $warehouse->name,
                'location' => $warehouse->location,
                'capacity' => $warehouse->capacity,
                'is_active' => $warehouse->is_active,
                'stock_movements_count' => $warehouse->stock_movements_count,
                'created_at' => $warehouse->created_at->format('M d, Y'),
            ],
            'recentMovements' => $recentMovements,
        ]);
    }
}
