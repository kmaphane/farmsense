<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use Domains\CRM\DTOs\SupplierData;
use Domains\CRM\Models\Supplier;
use Domains\Shared\Enums\SupplierCategory;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SupplierController extends Controller
{
    /**
     * Display a listing of suppliers.
     */
    public function index(): Response
    {
        $suppliers = Supplier::query()
            ->orderBy('name')
            ->paginate(15);

        return Inertia::render('Suppliers/Index', [
            'suppliers' => $suppliers->through(fn ($supplier) => [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'email' => $supplier->email,
                'phone' => $supplier->phone,
                'category' => $supplier->category->value,
                'category_label' => $supplier->category->label(),
                'performance_rating' => $supplier->performance_rating,
                'current_price_per_unit' => $supplier->current_price_per_unit,
                'is_active' => $supplier->is_active,
            ]),
            'pagination' => [
                'current_page' => $suppliers->currentPage(),
                'last_page' => $suppliers->lastPage(),
                'per_page' => $suppliers->perPage(),
                'total' => $suppliers->total(),
            ],
        ]);
    }

    /**
     * Display the specified supplier.
     */
    public function show(Supplier $supplier): Response
    {
        return Inertia::render('Suppliers/Show', [
            'supplier' => [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'email' => $supplier->email,
                'phone' => $supplier->phone,
                'category' => $supplier->category->value,
                'category_label' => $supplier->category->label(),
                'performance_rating' => $supplier->performance_rating,
                'rating_stars' => $supplier->rating_stars,
                'current_price_per_unit' => $supplier->current_price_per_unit,
                'notes' => $supplier->notes,
                'is_active' => $supplier->is_active,
                'created_at' => $supplier->created_at->format('M d, Y'),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified supplier.
     */
    public function edit(Supplier $supplier): Response
    {
        $supplierCategories = collect(SupplierCategory::cases())->map(fn ($category) => [
            'value' => $category->value,
            'label' => $category->label(),
        ])->toArray();

        return Inertia::render('Suppliers/Edit', [
            'supplier' => [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'email' => $supplier->email,
                'phone' => $supplier->phone,
                'category' => $supplier->category->value,
                'performance_rating' => $supplier->performance_rating,
                'current_price_per_unit' => $supplier->current_price_per_unit,
                'notes' => $supplier->notes,
                'is_active' => $supplier->is_active,
            ],
            'supplierCategories' => $supplierCategories,
        ]);
    }

    /**
     * Update the specified supplier.
     */
    public function update(SupplierData $data, Supplier $supplier): RedirectResponse
    {
        $supplier->update([
            'name' => $data->name,
            'email' => $data->email,
            'phone' => $data->phone,
            'category' => $data->category,
            'performance_rating' => $data->performance_rating,
            'current_price_per_unit' => $data->current_price_per_unit,
            'notes' => $data->notes,
            'is_active' => $data->is_active ?? true,
        ]);

        return redirect()
            ->route('suppliers.show', $supplier)
            ->with('success', 'Supplier updated successfully.');
    }
}
