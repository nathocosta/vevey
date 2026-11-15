<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Gate;
use Vevey\Catalog\Models\Attribute;
use Vevey\Catalog\Models\AttributeValue;
use Vevey\Catalog\Models\Category;
use Vevey\Catalog\Models\Product;
use Vevey\Permissions\Enums\Roles;

/**
 * @return array<string, bool>
 */
function catalogAbilities(Roles $role, object $record): array
{
    $gate = Gate::forUser(staffWithRole($role));

    return [
        'viewAny' => $gate->allows('viewAny', $record::class),
        'view' => $gate->allows('view', $record),
        'create' => $gate->allows('create', $record::class),
        'update' => $gate->allows('update', $record),
        'delete' => $gate->allows('delete', $record),
        'restore' => $gate->allows('restore', $record),
        'forceDelete' => $gate->allows('forceDelete', $record),
        'deleteAny' => $gate->allows('deleteAny', $record::class),
        'restoreAny' => $gate->allows('restoreAny', $record::class),
        'forceDeleteAny' => $gate->allows('forceDeleteAny', $record::class),
        'replicate' => $gate->allows('replicate', $record),
        'reorder' => $gate->allows('reorder', $record::class),
    ];
}

test('the owner manages products but never deletes them for good', function (): void {
    expect(catalogAbilities(Roles::Admin, Product::factory()->create()))->toBe([
        'viewAny' => true,
        'view' => true,
        'create' => true,
        'update' => true,
        'delete' => true,
        'restore' => true,
        'forceDelete' => false,
        'deleteAny' => true,
        'restoreAny' => true,
        'forceDeleteAny' => false,
        'replicate' => true,
        'reorder' => true,
    ]);
});

test('the owner manages categories, attributes and their values', function (Closure $makeRecord): void {
    expect(catalogAbilities(Roles::Admin, $makeRecord()))->toMatchArray([
        'viewAny' => true,
        'view' => true,
        'create' => true,
        'update' => true,
        'delete' => true,
        'deleteAny' => true,
        'replicate' => true,
        'reorder' => true,
    ]);
})->with([
    'category' => fn (): Category => Category::factory()->create(),
    'attribute' => fn (): Attribute => Attribute::factory()->create(),
    'attribute value' => fn (): AttributeValue => AttributeValue::factory()->create(),
]);

test('counter and logistics staff only read the catalog', function (Roles $role, Closure $makeRecord): void {
    expect(catalogAbilities($role, $makeRecord()))->toBe([
        'viewAny' => true,
        'view' => true,
        'create' => false,
        'update' => false,
        'delete' => false,
        'restore' => false,
        'forceDelete' => false,
        'deleteAny' => false,
        'restoreAny' => false,
        'forceDeleteAny' => false,
        'replicate' => false,
        'reorder' => false,
    ]);
})->with([Roles::Attendant, Roles::Logistics])->with([
    'product' => fn (): Product => Product::factory()->create(),
    'category' => fn (): Category => Category::factory()->create(),
    'attribute' => fn (): Attribute => Attribute::factory()->create(),
    'attribute value' => fn (): AttributeValue => AttributeValue::factory()->create(),
]);

test('the super admin can do everything, including deleting a product for good', function (): void {
    expect(catalogAbilities(Roles::SuperAdmin, Product::factory()->create()))->each->toBeTrue();
});
