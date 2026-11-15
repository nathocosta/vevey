<?php

declare(strict_types=1);

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Vevey\Catalog\Models\Product;

/*
 * The CHECK on prices and the full text search exist only on Postgres (see the products migration).
 * These run in CI, which tests against Postgres, and are skipped on the local SQLite database.
 */
test('the database refuses a promotional price that is not lower than the price', function (): void {
    expect(fn () => Product::factory()->onPromotion(brl(1250))->create(['price_cents' => brl(1250)]))
        ->toThrow(QueryException::class, 'products_prices_check');
})->skip(fn (): bool => DB::getDriverName() !== 'pgsql', 'Postgres only.');

test('the database refuses a price of zero', function (): void {
    expect(fn () => Product::factory()->create(['price_cents' => brl(0)]))
        ->toThrow(QueryException::class, 'products_prices_check');
})->skip(fn (): bool => DB::getDriverName() !== 'pgsql', 'Postgres only.');

test('search finds products by full text in Portuguese on the name and the description', function (): void {
    $cake = Product::factory()->create(['name' => 'Bolo de chocolate 1 kg', 'description' => null]);
    $plate = Product::factory()->create(['name' => 'Prato descartável azul', 'description' => 'Ideal para festas infantis']);
    Product::factory()->create(['name' => 'Copo descartável dourado', 'description' => null]);

    expect(Product::query()->search('bolos')->pluck('id')->all())->toBe([$cake->id])
        ->and(Product::query()->search('festa')->pluck('id')->all())->toBe([$plate->id]);
})->skip(fn (): bool => DB::getDriverName() !== 'pgsql', 'Postgres only.');

test('search runs on the generated tsvector instead of a LIKE', function (): void {
    $sql = Product::query()->search('bolo')->toRawSql();

    expect($sql)->toContain('("search_vector") @@ websearch_to_tsquery')
        ->and(str_contains($sql, ' like '))->toBeFalse();
})->skip(fn (): bool => DB::getDriverName() !== 'pgsql', 'Postgres only.');
