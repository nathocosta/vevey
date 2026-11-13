<?php

declare(strict_types=1);

use Filament\Actions\Testing\TestAction;
use Filament\Forms\Components\Select;
use Livewire\Livewire;
use Vevey\Catalog\Models\Attribute;
use Vevey\Catalog\Models\AttributeValue;
use Vevey\Catalog\Models\Category;
use Vevey\Catalog\Models\Product;
use Vevey\PanelAdmin\Filament\Resources\Attributes\AttributeResource;
use Vevey\PanelAdmin\Filament\Resources\Attributes\Pages\CreateAttribute;
use Vevey\PanelAdmin\Filament\Resources\Attributes\Pages\EditAttribute;
use Vevey\PanelAdmin\Filament\Resources\Categories\Pages\EditCategory;
use Vevey\PanelAdmin\Filament\Resources\Categories\Pages\ListCategories;
use Vevey\PanelAdmin\Filament\Resources\Products\ProductResource;
use Vevey\Permissions\Enums\Roles;

use function Pest\Laravel\actingAs;

test('the owner creates an attribute with its values in one form', function (): void {
    actingAs(staffWithRole(Roles::Admin));

    Livewire::test(CreateAttribute::class)
        ->fillForm([
            'name' => 'Cor',
            'slug' => 'cor',
            'values' => [
                ['value' => 'Azul', 'slug' => 'azul'],
                ['value' => 'Rosa', 'slug' => 'rosa'],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $attribute = Attribute::query()->where('slug', 'cor')->sole();

    expect($attribute->values->pluck('value')->sort()->values()->all())->toBe(['Azul', 'Rosa']);
});

test('a category parent can only be a top-level category other than itself', function (): void {
    actingAs(staffWithRole(Roles::Admin));
    $party = Category::factory()->create(['name' => 'Festa']);
    Category::factory()->childOf($party)->create(['name' => 'Balões']);
    $candy = Category::factory()->create(['name' => 'Doces']);

    Livewire::test(EditCategory::class, ['record' => $candy->getKey()])
        ->assertFormFieldExists('parent_id', fn (Select $field): bool => $field->getOptions() === [$party->id => 'Festa']);
});

test('a category in use is not deleted and the owner is told why', function (string $usedBy): void {
    actingAs(staffWithRole(Roles::Admin));
    $category = Category::factory()->create(['name' => 'Festa']);

    if ($usedBy === 'product') {
        $category->products()->attach(Product::factory()->create());
    } else {
        Category::factory()->childOf($category)->create();
    }

    Livewire::test(ListCategories::class)
        ->callAction(TestAction::make('delete')->table($category))
        ->assertNotified(__('panel-admin::actions.deletion_blocked'));

    expect(Category::query()->find($category->id))->not->toBeNull();
})->with(['product', 'subcategory']);

test('an unused category is deleted', function (): void {
    actingAs(staffWithRole(Roles::Admin));
    $category = Category::factory()->create();

    Livewire::test(ListCategories::class)
        ->callAction(TestAction::make('delete')->table($category));

    expect(Category::query()->find($category->id))->toBeNull();
});

test('an attribute whose values products use is not deleted and the owner is told which values', function (): void {
    actingAs(staffWithRole(Roles::Admin));
    $color = Attribute::factory()->create(['name' => 'Cor']);
    $blue = AttributeValue::factory()->for($color)->create(['value' => 'Azul']);
    $blue->products()->attach(Product::factory()->count(2)->create());

    Livewire::test(EditAttribute::class, ['record' => $color->getKey()])
        ->callAction('delete')
        ->assertNotified(__('panel-admin::actions.deletion_blocked'));

    expect(Attribute::query()->find($color->id))->not->toBeNull()
        ->and(AttributeResource::deletionBlockedReason($color))->toContain('Azul (2');
});

test('an attribute nobody uses is deleted with its values', function (): void {
    actingAs(staffWithRole(Roles::Admin));
    $value = AttributeValue::factory()->create();

    Livewire::test(EditAttribute::class, ['record' => $value->attribute_id])
        ->callAction('delete');

    expect(Attribute::query()->find($value->attribute_id))->toBeNull()
        ->and(AttributeValue::query()->find($value->id))->toBeNull();
});

test('removing a value in use from the attribute form fails with the value named', function (): void {
    actingAs(staffWithRole(Roles::Admin));
    $color = Attribute::factory()->create(['name' => 'Cor', 'slug' => 'cor']);
    $blue = AttributeValue::factory()->for($color)->create(['value' => 'Azul', 'slug' => 'azul']);
    $pink = AttributeValue::factory()->for($color)->create(['value' => 'Rosa', 'slug' => 'rosa']);
    $blue->products()->attach(Product::factory()->create());

    Livewire::test(EditAttribute::class, ['record' => $color->getKey()])
        ->callAction(TestAction::make('delete')->schemaComponent('values')->arguments(['item' => "record-{$blue->id}"]))
        ->call('save')
        ->assertHasFormErrors(['values'])
        ->assertSee('Azul (1');

    expect(AttributeValue::query()->find($blue->id))->not->toBeNull();
});

test('removing an unused value from the attribute form deletes it', function (): void {
    actingAs(staffWithRole(Roles::Admin));
    $color = Attribute::factory()->create(['name' => 'Cor', 'slug' => 'cor']);
    $blue = AttributeValue::factory()->for($color)->create(['value' => 'Azul', 'slug' => 'azul']);
    $pink = AttributeValue::factory()->for($color)->create(['value' => 'Rosa', 'slug' => 'rosa']);
    $blue->products()->attach(Product::factory()->create());

    Livewire::test(EditAttribute::class, ['record' => $color->getKey()])
        ->callAction(TestAction::make('delete')->schemaComponent('values')->arguments(['item' => "record-{$pink->id}"]))
        ->call('save')
        ->assertHasNoFormErrors();

    expect(AttributeValue::query()->find($pink->id))->toBeNull()
        ->and(AttributeValue::query()->find($blue->id))->not->toBeNull();
});

test('counter staff cannot reorder categories', function (): void {
    actingAs(staffWithRole(Roles::Attendant));

    Livewire::test(ListCategories::class)
        ->assertDontSeeHtml('wire:click="toggleTableReordering"');
});

test('the catalog resources are grouped under the catalog navigation', function (string $locale, string $group): void {
    app()->setLocale($locale);

    expect(ProductResource::getNavigationGroup())->toBe($group)
        ->and(ProductResource::getPluralModelLabel())->not->toStartWith('panel-admin::');
})->with([
    'en' => ['en', 'Catalog'],
    'pt_BR' => ['pt_BR', 'Catálogo'],
]);
