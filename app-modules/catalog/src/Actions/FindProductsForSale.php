<?php

declare(strict_types=1);

namespace Vevey\Catalog\Actions;

use Vevey\Catalog\DTOs\ProductData;
use Vevey\Catalog\Models\Product;

/**
 * Reads the products an order is about to sell. Inactive and deleted products are left out,
 * so the caller compares the returned ids with the ones it asked for to find what cannot be sold.
 */
final class FindProductsForSale
{
    /**
     * @param  list<string>  $productIds
     * @return array<string, ProductData> keyed by product id
     */
    public function handle(array $productIds): array
    {
        if ($productIds === []) {
            return [];
        }

        return Product::query()
            ->active()
            ->whereKey(array_values(array_unique($productIds)))
            ->with(['attributeValues.attribute', 'media'])
            ->get()
            ->mapWithKeys(fn (Product $product): array => [$product->id => ProductData::fromModel($product)])
            ->all();
    }
}
