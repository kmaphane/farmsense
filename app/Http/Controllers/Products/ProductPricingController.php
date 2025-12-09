<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use Domains\Inventory\Actions\UpdateProductPriceAction;
use Domains\Inventory\DTOs\ProductPriceUpdateData;
use Domains\Inventory\Enums\ProductType;
use Domains\Inventory\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProductPricingController extends Controller
{
    public function __construct(
        protected UpdateProductPriceAction $updatePriceAction
    ) {}

    /**
     * Display products with pricing and history.
     */
    public function index(): Response
    {
        $teamId = Auth::user()->current_team_id;

        // Get poultry products (products that are sold)
        $products = Product::query()
            ->where('team_id', $teamId)
            ->whereIn('type', [
                ProductType::LiveBird,
                ProductType::WholeChicken,
                ProductType::ChickenPieces,
                ProductType::Offal,
                ProductType::ByProduct,
            ])
            ->where('is_active', true)
            ->with(['priceHistory' => fn ($q) => $q->orderBy('effective_from', 'desc')->limit(5)])
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'type' => $product->type->value,
                'typeLabel' => $product->type->label(),
                'selling_price_cents' => $product->selling_price_cents,
                'selling_price_formatted' => 'P'.number_format($product->selling_price_cents / 100, 2),
                'units_per_package' => $product->units_per_package ? (float) $product->units_per_package : null,
                'package_unit' => $product->package_unit?->value,
                'package_unit_label' => $product->package_unit?->label(),
                'price_history' => $product->priceHistory->map(fn ($history) => [
                    'selling_price_cents' => $history->selling_price_cents,
                    'selling_price_formatted' => 'P'.number_format($history->selling_price_cents / 100, 2),
                    'effective_from' => $history->effective_from->toDateString(),
                    'effective_until' => $history->effective_until?->toDateString(),
                    'changed_by' => $history->changer?->name,
                    'notes' => $history->notes,
                ])->toArray(),
            ])
            ->toArray();

        return Inertia::render('Products/Pricing', [
            'products' => $products,
        ]);
    }

    /**
     * Update product price.
     */
    public function update(Product $product, ProductPriceUpdateData $data): RedirectResponse
    {
        // Authorization: ensure user can only update their team's products
        abort_if($product->team_id !== Auth::user()->current_team_id, 403, 'Unauthorized access to this product.');

        $this->updatePriceAction->execute($product, $data);

        return redirect()
            ->route('products.pricing')
            ->with('success', 'Product price updated successfully.');
    }
}
