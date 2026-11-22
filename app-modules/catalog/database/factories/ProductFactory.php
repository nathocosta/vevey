<?php

declare(strict_types=1);

namespace Vevey\Catalog\Database\Factories;

use App\Casts\MoneyCast;
use Brick\Money\Money;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Vevey\Catalog\Models\Product;

/** @extends Factory<Product> */
final class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = rtrim(fake()->unique()->sentence(3), '.');

        return [
            'sku' => mb_strtoupper(fake()->unique()->bothify('???-###-??')),
            'barcode' => fake()->optional()->ean13(),
            'name' => Str::ucfirst($name),
            'slug' => Str::slug($name),
            'description' => fake()->optional()->paragraph(),
            'price_cents' => Money::ofMinor(fake()->numberBetween(100, 50_000), MoneyCast::CURRENCY),
            'promo_price_cents' => null,
            'is_active' => true,
        ];
    }

    public function inactive(): self
    {
        return $this->state(['is_active' => false]);
    }

    public function onPromotion(Money $promoPrice): self
    {
        return $this->state(['promo_price_cents' => $promoPrice]);
    }
}
