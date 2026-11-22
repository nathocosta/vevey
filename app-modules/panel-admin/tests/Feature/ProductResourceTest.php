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

test('counter staff cannot duplicate, bulk delete or bulk restore products', function (): void {
    actingAs(staffWithRole(Roles::Attendant));
    $product = Product::factory()->create();

    Livewire::test(ListProducts::class)
        ->assertActionHidden(TestAction::make('duplicate')->table($product))
        ->assertActionHidden(TestAction::make('delete')->table($product))
        ->assertActionHidden(TestAction::make('delete')->table()->bulk())
        ->assertActionHidden(TestAction::make('restore')->table()->bulk());
});

test('the panel offers no way to delete a product for good', function (): void {
    actingAs(staffWithRole(Roles::SuperAdmin));
    $product = Product::factory()->create();
    $product->delete();

    Livewire::test(ListProducts::class)
        ->assertActionDoesNotExist(TestAction::make('forceDelete')->table($product))
        ->assertActionDoesNotExist(TestAction::make('forceDelete')->table()->bulk());

    Livewire::test(EditProduct::class, ['record' => $product->getKey()])
        ->assertActionDoesNotExist('forceDelete');
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

    expect($product->price_cents)->toEqual(brl(1250))
        ->and($product->promo_price_cents)->toEqual(brl(990))
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

test('prices take at most two decimals and stay within the limit', function (string $price, string $rule): void {
    actingAs(staffWithRole(Roles::Admin));

    Livewire::test(CreateProduct::class)
        ->fillForm([
            'name' => 'Vela de aniversário',
            'slug' => 'vela-de-aniversario',
            'sku' => 'VELA-NUM-1',
            'price_cents' => $price,
        ])
        ->call('create')
        ->assertHasFormErrors(['price_cents' => $rule]);
})->with([
    'three decimals' => ['9.995', 'decimal'],
    'above the limit' => ['99999999999999999', 'max'],
    'zero' => ['0', 'min'],
]);

test('editing a product shows the stored cents as reais and saves them back as cents', function (): void {
    actingAs(staffWithRole(Roles::Admin));
    $product = Product::factory()->create(['price_cents' => brl(1250)]);

    Livewire::test(EditProduct::class, ['record' => $product->getKey()])
        ->assertSchemaStateSet(['price_cents' => '12.50', 'promo_price_cents' => null])
        ->fillForm(['promo_price_cents' => '11.99'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($product->fresh()?->price_cents)->toEqual(brl(1250))
        ->and($product->fresh()?->promo_price_cents)->toEqual(brl(1199));
});

test('duplicating a product copies prices, categories and attributes under a new SKU', function (): void {
    actingAs(staffWithRole(Roles::Admin));
    $category = Category::factory()->create();
    $blue = AttributeValue::factory()->create();
    $original = Product::factory()->create(['price_cents' => brl(1250), 'barcode' => '7891234567895']);
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
        ->and($copy->price_cents)->toEqual(brl(1250))
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

test('the list searches by name, or by SKU and barcode exactly', function (): void {
    actingAs(staffWithRole(Roles::Attendant));
    $balloon = Product::factory()->create(['name' => 'Balão metalizado azul', 'sku' => 'BAL-MET-9-AZ', 'barcode' => '7891234567895']);
    $cake = Product::factory()->create(['name' => 'Bolo de chocolate', 'sku' => 'BOLO-CHOC-1KG', 'barcode' => null]);

    Livewire::test(ListProducts::class)
        ->searchTable('Bolo')
        ->assertCanSeeTableRecords([$cake])
        ->assertCanNotSeeTableRecords([$balloon])
        ->searchTable('bal-met-9-az')
        ->assertCanSeeTableRecords([$balloon])
        ->assertCanNotSeeTableRecords([$cake])
        ->searchTable('7891234567895')
        ->assertCanSeeTableRecords([$balloon])
        ->assertCanNotSeeTableRecords([$cake]);
});

test('global search finds products through the catalog search', function (): void {
    actingAs(staffWithRole(Roles::Attendant));
    Product::factory()->create(['name' => 'Bolo de chocolate', 'sku' => 'BOLO-CHOC-1KG']);
    Product::factory()->create(['name' => 'Balão metalizado azul', 'sku' => 'BAL-MET-9-AZ']);

    expect(ProductResource::getGlobalSearchResults('BOLO-CHOC-1KG')->pluck('title')->all())->toBe(['Bolo de chocolate']);
});

test('the list filters by category, active and trashed', function (): void {
    actingAs(staffWithRole(Roles::Admin));
    $cakes = Category::factory()->create();
    $cake = Product::factory()->create();
    $cake->categories()->attach($cakes);
    $inactive = Product::factory()->inactive()->create();
    $trashed = Product::factory()->create();
    $trashed->delete();

    Livewire::test(ListProducts::class)
        ->filterTable('categories', [$cakes->id])
        ->assertCanSeeTableRecords([$cake])
        ->assertCanNotSeeTableRecords([$inactive, $trashed])
        ->resetTableFilters()
        ->filterTable('is_active', false)
        ->assertCanSeeTableRecords([$inactive])
        ->assertCanNotSeeTableRecords([$cake])
        ->resetTableFilters()
        ->filterTable('trashed', true)
        ->assertCanSeeTableRecords([$cake, $inactive, $trashed]);
});
