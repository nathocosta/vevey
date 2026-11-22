<?php

declare(strict_types=1);

namespace Vevey\Catalog\DTOs;

use Brick\Money\Money;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Vevey\Catalog\Models\Product;

/**
 * A product as another module sees it, e.g. orders taking the snapshot of an order line (ADR 3).
 * salePrice is the price charged now (promotional when there is one).
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
        public Money $price,
        public ?Money $promoPrice,
        public Money $salePrice,
        public ?string $thumbnailUrl,
        public array $attributes,
    ) {}

    public static function fromModel(Product $product): self
    {
        return new self(
            id: $product->id,
            sku: $product->sku,
            barcode: $product->barcode,
            name: $product->name,
            description: $product->description,
            price: $product->price_cents,
            promoPrice: $product->promo_price_cents,
            salePrice: $product->sale_price,
            thumbnailUrl: self::thumbnailUrl($product),
            attributes: array_values($product->attributeValues->map(ProductAttributeData::fromModel(...))->all()),
        );
    }

    /**
     * The first image's thumbnail. The thumbnail is generated on the queue, so until then the original image is used
     * instead of a URL that does not exist yet.
     */
    private static function thumbnailUrl(Product $product): ?string
    {
        $image = $product->getFirstMedia(Product::IMAGES_COLLECTION);

        if (! $image instanceof Media) {
            return null;
        }

        return $image->hasGeneratedConversion(Product::THUMB_CONVERSION)
            ? $image->getUrl(Product::THUMB_CONVERSION)
            : $image->getUrl();
    }
}
