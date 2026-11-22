<?php

declare(strict_types=1);

use Illuminate\Database\QueryException;
use Vevey\Catalog\Models\AttributeValue;
use Vevey\Catalog\Models\Category;
use Vevey\Catalog\Models\Product;

test('a subcategory cannot be the parent of another category', function (): void {
    $party = Category::factory()->create();
    $balloons = Category::factory()->childOf($party)->create();

    expect(fn () => Category::factory()->childOf($balloons)->create())
        ->toThrow(DomainException::class, 'one level of nesting');
});

test('a category with subcategories cannot get a parent', function (): void {
    $party = Category::factory()->create();
    Category::factory()->childOf($party)->create();
    $candy = Category::factory()->create();

    expect(fn () => $party->update(['parent_id' => $candy->id]))
        ->toThrow(DomainException::class, 'one level of nesting');
});

test('a category cannot be its own parent', function (): void {
    $party = Category::factory()->create();

    expect(fn () => $party->update(['parent_id' => $party->id]))
        ->toThrow(DomainException::class, 'its own parent');
});

test('the database refuses to delete a category or a value that products use', function (string $model): void {
    $product = Product::factory()->create();
    $record = $model === 'category' ? Category::factory()->create() : AttributeValue::factory()->create();
    $model === 'category' ? $product->categories()->attach($record) : $product->attributeValues()->attach($record);

    expect(fn () => $record->delete())->toThrow(QueryException::class);
})->with(['category', 'attribute value']);
