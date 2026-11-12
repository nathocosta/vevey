<?php

declare(strict_types=1);

namespace Vevey\Catalog\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute as AttributeCast;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Vevey\Catalog\Database\Factories\ProductFactory;
use Vevey\Catalog\Policies\ProductPolicy;

/**
 * Every color is its own product (flat catalog, ADR 6). Prices in integer cents (ADR 1).
 *
 * @property string $id
 * @property string $sku
 * @property string|null $barcode
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int $price_cents
 * @property int|null $promo_price_cents
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read int $sale_price_cents
 * @property-read Collection<int, Category> $categories
 * @property-read Collection<int, AttributeValue> $attributeValues
 * @property-read Collection<int, Media> $media
 */
#[UseFactory(ProductFactory::class)]
#[UsePolicy(ProductPolicy::class)]
#[Fillable(['sku', 'barcode', 'name', 'slug', 'description', 'price_cents', 'promo_price_cents', 'is_active'])]
#[Table(name: 'products')]
final class Product extends Model implements HasMedia
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    use HasUuids;
    use InteractsWithMedia;
    use SoftDeletes;

    public const string IMAGES_COLLECTION = 'images';

    public const string THUMB_CONVERSION = 'thumb';

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * @return BelongsToMany<AttributeValue, $this>
     */
    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::IMAGES_COLLECTION)
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion(self::THUMB_CONVERSION)
            ->fit(Fit::Crop, 400, 400)
            ->format('webp');
    }

    /**
     * The price charged now: the promotional price when there is one, otherwise the regular price.
     *
     * @return AttributeCast<int, never>
     */
    protected function salePriceCents(): AttributeCast
    {
        return AttributeCast::get(fn (): int => $this->promo_price_cents ?? $this->price_cents);
    }

    /**
     * @param  Builder<Product>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_cents' => 'integer',
            'promo_price_cents' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
