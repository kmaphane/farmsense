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
        protected UpdateProductPriceAction $updateProductPriceAction
    ) {}

    /**
     * Display product pricing list with history.
     */
    public function index(): Response
    {
        $teamId = Auth::user()->current_team_id;

        // Get all sellable products (poultry types with prices)
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
            ->with(['priceHistory' => fn ($q) => $q->orderByDesc('effective_from')->limit(5)])
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'local_name' => $product->local_name,
                'type' => $product->type->value,
                'type_label' => $product->type->label(),
                'selling_price_cents' => $product->selling_price_cents,
                'selling_price_formatted' => $product->selling_price_cents
                    ? 'P '.number_format($product->selling_price_cents / 100, 2)
                    : null,
                'units_per_package' => (float) ($product->units_per_package ?? 1),
                'package_unit' => $product->package_unit?->value,
                'package_unit_label' => $product->package_unit?->label(),
                'price_history' => $product->priceHistory->map(fn ($history) => [
                    'id' => $history->id,
                    'price_cents' => $history->price_cents,
                    'price_formatted' => 'P '.number_format($history->price_cents / 100, 2),
                    'effective_from' => $history->effective_from->toDateTimeString(),
                    'effective_until' => $history->effective_until?->toDateTimeString(),
                    'changed_by' => $history->changedBy?->name,
                    'reason' => $history->reason,
                ]),
            ]);

        return Inertia::render('Products/Pricing', [
            'products' => $products,
        ]);
    }

    /**
     * Update a product's price.
     */
    public function update(Product $product, ProductPriceUpdateData $data): RedirectResponse
    {
        // Authorization: ensure user can only update their team's products
        abort_if($product->team_id !== Auth::user()->current_team_id, 403, 'Unauthorized access to this product.');

        $this->updateProductPriceAction->execute(
            product: $product,
            newPriceCents: $data->new_price_cents,
            changedBy: Auth::id(),
            reason: $data->reason,
        );

        $newPrice = number_format($data->new_price_cents / 100, 2);

        return back()->with('success', "Price for {$product->name} updated to P{$newPrice}.");
    }
}
