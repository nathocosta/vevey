<?php

declare(strict_types=1);

use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use Vevey\Catalog\Models\AttributeValue;
use Vevey\Catalog\Models\Category;
use Vevey\Catalog\Models\Product;
use Vevey\PanelAdmin\Filament\Resources\Products\Pages\CreateProduct;
use Vevey\PanelAdmin\Filament\Resources\Products\Pages\EditProduct;
use Vevey\PanelAdmin\Filament\Resources\Products\Pages\ListProducts;
use Vevey\PanelAdmin\Filament\Resources\Products\ProductResource;
use Vevey\Permissions\Enums\Roles;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('counter staff list the products but cannot create one', function (): void {
    actingAs(staffWithRole(Roles::Attendant));
    $products = Product::factory()->count(2)->create();

    Livewire::test(ListProducts::class)
        ->assertOk()
        ->assertCanSeeTableRecords($products)
        ->assertActionHidden('create');

    get(ProductResource::getUrl('create'))->assertForbidden();
});

test('the owner creates a product with prices typed in reais', function (): void {
    actingAs(staffWithRole(Roles::Admin));
    $category = Category::factory()->create();
    $blue = AttributeValue::factory()->create();

    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => 'Balão metalizado 9" azul',
            'slug' => 'balao-metalizado-9-azul',
            'sku' => 'BAL-MET-9-AZ',
            'price_cents' => '12.50',
            'promo_price_cents' => '9.90',
            'categories' => [$category->id],
            'attributeValues' => [$blue->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::query()->where('sku', 'BAL-MET-9-AZ')->sole();

    expect($product->price_cents)->toBe(1250)
        ->and($product->promo_price_cents)->toBe(990)
        ->and($product->categories->modelKeys())->toBe([$category->id])
        ->and($product->attributeValues->modelKeys())->toBe([$blue->id]);
});

test('the promotional price must be lower than the price', function (): void {
    actingAs(staffWithRole(Roles::Admin));

    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => 'Prato descartável azul',
            'slug' => 'prato-descartavel-azul',
            'sku' => 'PRT-DESC-AZ',
            'price_cents' => '10.00',
            'promo_price_cents' => '10.00',
        ])
        ->call('create')
        ->assertHasFormErrors(['promo_price_cents' => 'lt']);
});

test('the edit form shows the stored cents as reais', function (): void {
    actingAs(staffWithRole(Roles::Admin));
    $product = Product::factory()->create(['price_cents' => 1250]);

    Livewire::test(EditProduct::class, ['record' => $product->getKey()])
        ->assertSchemaStateSet(['price_cents' => '12.50', 'promo_price_cents' => null]);
});

test('duplicating a product copies prices, categories and attributes under a new SKU', function (): void {
    actingAs(staffWithRole(Roles::Admin));
    $category = Category::factory()->create();
    $blue = AttributeValue::factory()->create();
    $original = Product::factory()->create(['price_cents' => 1250, 'barcode' => '7891234567895']);
    $original->categories()->attach($category);
    $original->attributeValues()->attach($blue);

    Livewire::test(ListProducts::class)
        ->callAction(TestAction::make('duplicate')->table($original), [
            'name' => 'Balão metalizado 9" rosa',
            'slug' => 'balao-metalizado-9-rosa',
            'sku' => 'BAL-MET-9-RS',
        ])
        ->assertHasNoFormErrors();

    $copy = Product::query()->where('sku', 'BAL-MET-9-RS')->sole();

    expect($copy->id)->not->toBe($original->id)
        ->and($copy->price_cents)->toBe(1250)
        ->and($copy->barcode)->toBeNull()
        ->and($copy->categories->modelKeys())->toBe([$category->id])
        ->and($copy->attributeValues->modelKeys())->toBe([$blue->id]);
});

test('a duplicate cannot reuse the SKU of another product', function (): void {
    actingAs(staffWithRole(Roles::Admin));
    $original = Product::factory()->create(['sku' => 'BAL-MET-9-AZ']);

    Livewire::test(ListProducts::class)
        ->callAction(TestAction::make('duplicate')->table($original), [
            'name' => 'Outro',
            'slug' => 'outro',
            'sku' => 'BAL-MET-9-AZ',
        ])
        ->assertHasFormErrors(['sku' => 'unique']);
});

test('deleting a product keeps it in the database', function (): void {
    actingAs(staffWithRole(Roles::Admin));
    $product = Product::factory()->create();

    Livewire::test(EditProduct::class, ['record' => $product->getKey()])
        ->callAction('delete');

    expect(Product::withTrashed()->find($product->id)?->trashed())->toBeTrue();
});
