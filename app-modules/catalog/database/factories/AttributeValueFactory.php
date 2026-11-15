<?php

declare(strict_types=1);

namespace Vevey\Catalog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Vevey\Catalog\Models\Attribute;
use Vevey\Catalog\Models\AttributeValue;

/** @extends Factory<AttributeValue> */
final class AttributeValueFactory extends Factory
{
    protected $model = AttributeValue::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $value = fake()->unique()->colorName();

        return [
            'attribute_id' => Attribute::factory(),
            'value' => $value,
            'slug' => Str::slug($value),
        ];
    }
}
