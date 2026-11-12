<?php

declare(strict_types=1);

namespace Vevey\Catalog\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Vevey\Catalog\Models\Attribute;
use Vevey\Catalog\Models\AttributeValue;
use Vevey\Catalog\Models\Category;
use Vevey\Catalog\Models\Product;

final class CatalogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Relation::morphMap([
            'products' => Product::class,
            'categories' => Category::class,
            'attributes' => Attribute::class,
            'attribute_values' => AttributeValue::class,
        ]);
    }
}
