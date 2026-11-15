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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Vevey\Catalog\Database\Factories\AttributeFactory;
use Vevey\Catalog\Policies\AttributePolicy;

/**
 * A filter dimension of the catalog, such as Cor or Tipo (ADR 6).
 *
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, AttributeValue> $values
 */
#[UseFactory(AttributeFactory::class)]
#[UsePolicy(AttributePolicy::class)]
#[Fillable(['name', 'slug'])]
#[Table(name: 'attributes')]
final class Attribute extends Model
{
    /** @use HasFactory<AttributeFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * @return HasMany<AttributeValue, $this>
     */
    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class);
    }

    /**
     * The values of this attribute that products use, each with `products_count`.
     * An attribute with any of them cannot be deleted.
     *
     * @return Collection<int, AttributeValue>
     */
    public function valuesInUse(): Collection
    {
        return $this->values()
            ->whereHas('products')
            ->withCount('products')
            ->orderBy('value')
            ->get();
    }
}
