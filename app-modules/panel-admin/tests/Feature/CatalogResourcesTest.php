<?php

declare(strict_types=1);

use Filament\Forms\Components\Select;
use Livewire\Livewire;
use Vevey\Catalog\Models\Attribute;
use Vevey\Catalog\Models\Category;
use Vevey\PanelAdmin\Filament\Resources\Attributes\Pages\CreateAttribute;
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

test('a category with children cannot be deleted', function (): void {
    actingAs(staffWithRole(Roles::Admin));
    $party = Category::factory()->create();
    Category::factory()->childOf($party)->create();

    Livewire::test(ListCategories::class)
        ->assertTableActionHidden('delete', $party);
});

test('the catalog resources are grouped under the catalog navigation', function (string $locale, string $group): void {
    app()->setLocale($locale);

    expect(ProductResource::getNavigationGroup())->toBe($group)
        ->and(ProductResource::getPluralModelLabel())->not->toStartWith('panel-admin::');
})->with([
    'en' => ['en', 'Catalog'],
    'pt_BR' => ['pt_BR', 'Catálogo'],
]);
