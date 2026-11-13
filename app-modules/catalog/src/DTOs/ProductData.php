<?php

declare(strict_types=1);

namespace Vevey\Catalog\DTOs;

use Vevey\Catalog\Models\Product;

/**
 * A product as another module sees it, e.g. orders taking the snapshot of an order line (ADR 3).
 * Prices are integer cents; salePriceCents is the price charged now (promotional when there is one).
 */
final readonly class ProductData
{
    /**
     * @param  list<ProductAttributeData>  $attributes
     */
    public function __construct(
        public string $id,
        public string $sku,
        public ?string $barcode,
        public string $name,
        public ?string $description,
        public int $priceCents,
        public ?int $promoPriceCents,
        public int $salePriceCents,
        public ?string $thumbnailUrl,
        public array $attributes,
    ) {}

    public static function fromModel(Product $product): self
    {
        $thumbnailUrl = $product->getFirstMediaUrl(Product::IMAGES_COLLECTION, Product::THUMB_CONVERSION);

        return new self(
            id: $product->id,
            sku: $product->sku,
            barcode: $product->barcode,
            name: $product->name,
            description: $product->description,
            priceCents: $product->price_cents,
            promoPriceCents: $product->promo_price_cents,
            salePriceCents: $product->sale_price_cents,
            thumbnailUrl: $thumbnailUrl !== '' ? $thumbnailUrl : null,
            attributes: array_values($product->attributeValues->map(ProductAttributeData::fromModel(...))->all()),
        );
    }
}
