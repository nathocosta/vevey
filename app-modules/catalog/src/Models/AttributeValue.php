<?php

declare(strict_types=1);

namespace Vevey\Catalog\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Vevey\Catalog\Database\Factories\AttributeValueFactory;
use Vevey\Catalog\Policies\AttributeValuePolicy;

/**
 * @property string $id
 * @property string $attribute_id
 * @property string $value
 * @property string $slug
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Attribute $attribute
 * @property-read Collection<int, Product> $products
 */
#[UseFactory(AttributeValueFactory::class)]
#[UsePolicy(AttributeValuePolicy::class)]
#[Fillable(['attribute_id', 'value', 'slug'])]
#[Table(name: 'attribute_values')]
final class AttributeValue extends Model
{
    /** @use HasFactory<AttributeValueFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * @return BelongsTo<Attribute, $this>
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    /**
     * @return BelongsToMany<Product, $this>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    /**
     * A value used by products cannot be deleted: products would lose it silently.
     */
    public function isInUse(): bool
    {
        return $this->products()->exists();
    }
}
