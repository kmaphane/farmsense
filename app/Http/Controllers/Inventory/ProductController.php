<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use Domains\Inventory\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::where('team_id', Auth::user()->current_team_id ?? Auth::user()->team_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Products/Index', [
            'products' => $products,
        ]);
    }

    public function create()
    {
        return Inertia::render('Products/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string'],
            'unit' => ['required', 'string', 'max:50'],
            'selling_price_cents' => ['nullable', 'integer', 'min:0'],
            'units_per_package' => ['nullable', 'integer', 'min:1'],
            'package_unit' => ['nullable', 'string'],
            'yield_per_bird' => ['nullable', 'integer', 'min:0'],
            'quantity_on_hand' => ['nullable', 'integer', 'min:0'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
            'unit_cost' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $validated['team_id'] = Auth::user()->current_team_id ?? Auth::user()->team_id;
        $validated['is_active'] = $validated['is_active'] ?? true;

        $product = Product::create($validated);

        return Redirect::route('products.index')->with('success', 'Product created successfully!');
    }
}
