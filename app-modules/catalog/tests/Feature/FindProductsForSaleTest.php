<?php

declare(strict_types=1);

use Vevey\Catalog\Actions\FindProductsForSale;
use Vevey\Catalog\DTOs\ProductAttributeData;
use Vevey\Catalog\DTOs\ProductData;
use Vevey\Catalog\Models\Attribute;
use Vevey\Catalog\Models\AttributeValue;
use Vevey\Catalog\Models\Product;

test('it returns the products for sale keyed by id', function (): void {
    $color = Attribute::factory()->create(['name' => 'Cor', 'slug' => 'cor']);
    $blue = AttributeValue::factory()->for($color)->create(['value' => 'Azul', 'slug' => 'azul']);
    $balloon = Product::factory()->create([
        'sku' => 'BAL-MET-9-AZ',
        'name' => 'Balão metalizado 9" azul',
        'price_cents' => 1250,
    ]);
    $balloon->attributeValues()->attach($blue);

    $products = resolve(FindProductsForSale::class)->handle([$balloon->id]);

    expect($products)->toHaveKey($balloon->id)
        ->and($products[$balloon->id])->toEqual(new ProductData(
            id: $balloon->id,
            sku: 'BAL-MET-9-AZ',
            barcode: $balloon->barcode,
            name: 'Balão metalizado 9" azul',
            description: $balloon->description,
            priceCents: 1250,
            promoPriceCents: null,
            salePriceCents: 1250,
            thumbnailUrl: null,
            attributes: [new ProductAttributeData(attribute: 'Cor', value: 'Azul')],
        ));
});

test('the sale price is the promotional price when there is one', function (): void {
    $product = Product::factory()->onPromotion(900)->create(['price_cents' => 1250]);

    $products = resolve(FindProductsForSale::class)->handle([$product->id]);

    expect($products[$product->id]->salePriceCents)->toBe(900)
        ->and($products[$product->id]->priceCents)->toBe(1250);
});

test('inactive and deleted products are not for sale', function (): void {
    $forSale = Product::factory()->create();
    $inactive = Product::factory()->inactive()->create();
    $deleted = Product::factory()->create();
    $deleted->delete();

    $products = resolve(FindProductsForSale::class)->handle([$forSale->id, $inactive->id, $deleted->id]);

    expect(array_keys($products))->toBe([$forSale->id]);
});

test('no ids means no products', function (): void {
    Product::factory()->create();

    expect(resolve(FindProductsForSale::class)->handle([]))->toBe([]);
});

test('ids that are not UUIDs are left out instead of failing the query', function (): void {
    $product = Product::factory()->create();

    $products = resolve(FindProductsForSale::class)->handle(['not-a-uuid', $product->id]);

    expect(array_keys($products))->toBe([$product->id]);
});
