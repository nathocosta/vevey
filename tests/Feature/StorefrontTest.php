<?php

declare(strict_types=1);

use Vevey\Catalog\Models\Category;
use Vevey\Catalog\Models\Product;

it('renders the commercial storefront home with active and promotional products', function (): void {
    $category = Category::factory()->create(['name' => 'Doces', 'slug' => 'doces', 'is_active' => true]);
    $active = Product::factory()->create([
        'name' => 'Caixa KitKat ao Leite 41,5g (24 unidades)',
        'price_cents' => brl(7490),
        'promo_price_cents' => brl(6490),
        'is_active' => true,
    ]);
    $active->categories()->attach($category);

    $inactive = Product::factory()->create([
        'name' => 'Produto oculto',
        'is_active' => false,
    ]);

    $this->get(route('store.home'))
        ->assertOk()
        ->assertSee('Caixa KitKat ao Leite 41,5g (24 unidades)')
        ->assertSee('R$ 64,90')
        ->assertDontSee($inactive->name);
});

it('filters the catalog by category, promo, and search term', function (): void {
    $parent = Category::factory()->create(['name' => 'Festa', 'slug' => 'festa', 'is_active' => true]);
    $child = Category::factory()->create(['name' => 'Balões', 'slug' => 'baloes', 'parent_id' => $parent->id, 'is_active' => true]);

    $promoBalloon = Product::factory()->create([
        'name' => 'Balão metalizado 9" azul',
        'price_cents' => brl(1250),
        'promo_price_cents' => brl(990),
        'is_active' => true,
    ]);
    $promoBalloon->categories()->attach($child);

    $regularBox = Product::factory()->create([
        'name' => 'Caixa de papelão kraft para doces (25 unidades)',
        'price_cents' => brl(3890),
        'promo_price_cents' => null,
        'is_active' => true,
    ]);

    $this->get(route('store.catalog', ['categoria' => 'festa']))
        ->assertOk()
        ->assertSee($promoBalloon->name)
        ->assertDontSee($regularBox->name);

    $this->get(route('store.catalog', ['ofertas' => 1]))
        ->assertOk()
        ->assertSee($promoBalloon->name)
        ->assertDontSee($regularBox->name);

    $this->get(route('store.catalog', ['q' => 'papelão']))
        ->assertOk()
        ->assertSee($regularBox->name)
        ->assertDontSee($promoBalloon->name);
});

it('renders the product detail page for active products and returns 404 for inactive ones', function (): void {
    $active = Product::factory()->create([
        'name' => 'Balas Fini Beijos de Morango (pacote 500g)',
        'slug' => 'balas-fini-beijos-de-morango-pacote-500g',
        'price_cents' => brl(2690),
        'is_active' => true,
    ]);

    $inactive = Product::factory()->create([
        'slug' => 'produto-inativo',
        'is_active' => false,
    ]);

    $this->get(route('store.product', ['slug' => $active->slug]))
        ->assertOk()
        ->assertSee($active->name)
        ->assertSee('R$ 26,90')
        ->assertSee($active->sku);

    $this->get(route('store.product', ['slug' => $inactive->slug]))
        ->assertNotFound();
});
