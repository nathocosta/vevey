<?php

declare(strict_types=1);

namespace Vevey\Catalog\Database\Seeders;

use App\Casts\MoneyCast;
use Brick\Money\Money;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Vevey\Catalog\Models\Attribute;
use Vevey\Catalog\Models\AttributeValue;
use Vevey\Catalog\Models\Category;
use Vevey\Catalog\Models\Product;

/**
 * Sample catalog for local manual testing: the examples of ADR 6 plus a few sweets.
 */
final class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $values = $this->seedAttributes([
            'Cor' => ['Azul', 'Rosa', 'Dourado', 'Branco'],
            'Tipo' => ['Metalizado', 'Látex'],
            'Tamanho' => ['9 polegadas', '11 polegadas'],
        ]);

        $party = $this->category('Festa', position: 1);
        $balloons = $this->category('Balões', $party, position: 1);
        $disposables = $this->category('Descartáveis', $party, position: 2);
        $sweets = $this->category('Doces', position: 2);
        $chocolates = $this->category('Chocolates', $sweets, position: 1);
        $candies = $this->category('Balas e Guloseimas', $sweets, position: 2);
        $packaging = $this->category('Embalagens e Papelões', position: 3);

        /** @var list<array{sku: string, name: string, price: int, promo?: int, barcode?: string, categories: list<Category>, values: list<string>, active?: bool}> $products */
        $products = [
            ['sku' => 'BAL-MET-9-AZ', 'name' => 'Balão metalizado 9" azul', 'price' => 1250, 'promo' => 990, 'barcode' => '7891234567895', 'categories' => [$balloons], 'values' => ['Cor:Azul', 'Tipo:Metalizado', 'Tamanho:9 polegadas']],
            ['sku' => 'BAL-MET-9-RS', 'name' => 'Balão metalizado 9" rosa', 'price' => 1250, 'categories' => [$balloons], 'values' => ['Cor:Rosa', 'Tipo:Metalizado', 'Tamanho:9 polegadas']],
            ['sku' => 'BAL-MET-9-DR', 'name' => 'Balão metalizado 9" dourado', 'price' => 1390, 'categories' => [$balloons], 'values' => ['Cor:Dourado', 'Tipo:Metalizado', 'Tamanho:9 polegadas']],
            ['sku' => 'BAL-LTX-11-AZ', 'name' => 'Balão látex 11" azul (pacote com 50)', 'price' => 2490, 'categories' => [$balloons], 'values' => ['Cor:Azul', 'Tipo:Látex', 'Tamanho:11 polegadas']],
            ['sku' => 'BAL-LTX-11-BR', 'name' => 'Balão látex 11" branco (pacote com 50)', 'price' => 2490, 'categories' => [$balloons], 'values' => ['Cor:Branco', 'Tipo:Látex', 'Tamanho:11 polegadas'], 'active' => false],
            ['sku' => 'PRT-DESC-AZ', 'name' => 'Prato descartável azul (10 unidades)', 'price' => 890, 'categories' => [$disposables], 'values' => ['Cor:Azul']],
            ['sku' => 'PRT-DESC-RS', 'name' => 'Prato descartável rosa (10 unidades)', 'price' => 890, 'categories' => [$disposables], 'values' => ['Cor:Rosa']],
            ['sku' => 'COP-DESC-DR', 'name' => 'Copo descartável dourado (10 unidades)', 'price' => 1090, 'categories' => [$disposables], 'values' => ['Cor:Dourado']],
            ['sku' => 'DOC-KITKAT-CX24', 'name' => 'Caixa KitKat ao Leite 41,5g (24 unidades)', 'price' => 7490, 'promo' => 6490, 'categories' => [$chocolates], 'values' => []],
            ['sku' => 'DOC-BIS-CX20', 'name' => 'Caixa Lacta Bis Ao Leite 100,8g (20 unidades)', 'price' => 11900, 'promo' => 10490, 'categories' => [$chocolates], 'values' => []],
            ['sku' => 'DOC-BALA-FINI-500', 'name' => 'Balas Fini Beijos de Morango (pacote 500g)', 'price' => 2690, 'categories' => [$candies], 'values' => []],
            ['sku' => 'DOC-PIRU-SORT-50', 'name' => 'Pirulito Pop Sortido (pacote com 50 unidades)', 'price' => 2190, 'categories' => [$candies], 'values' => []],
            ['sku' => 'EMB-PAP-DOCE-25', 'name' => 'Caixa de papelão kraft para doces e kits (25 unidades)', 'price' => 3890, 'categories' => [$packaging, $party], 'values' => []],
        ];

        foreach ($products as $data) {
            $product = Product::query()->create([
                'sku' => $data['sku'],
                'barcode' => $data['barcode'] ?? null,
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'description' => sprintf('%s. Produto de exemplo para testes locais.', $data['name']),
                'price_cents' => Money::ofMinor($data['price'], MoneyCast::CURRENCY),
                'promo_price_cents' => isset($data['promo']) ? Money::ofMinor($data['promo'], MoneyCast::CURRENCY) : null,
                'is_active' => $data['active'] ?? true,
            ]);

            $product->categories()->attach(array_map(fn (Category $category): string => $category->id, $data['categories']));
            $product->attributeValues()->attach(array_map(fn (string $key): string => $values[$key]->id, $data['values']));
        }

        Product::query()->where('sku', 'BAL-MET-9-DR')->sole()->delete();
    }

    /**
     * @param  array<string, list<string>>  $attributes
     * @return array<string, AttributeValue> keyed by "Attribute:Value"
     */
    private function seedAttributes(array $attributes): array
    {
        $values = [];

        foreach ($attributes as $name => $attributeValues) {
            $attribute = Attribute::query()->create(['name' => $name, 'slug' => Str::slug($name)]);

            foreach ($attributeValues as $value) {
                $values["{$name}:{$value}"] = $attribute->values()->create(['value' => $value, 'slug' => Str::slug($value)]);
            }
        }

        return $values;
    }

    private function category(string $name, ?Category $parent = null, int $position = 0): Category
    {
        return Category::query()->create([
            'parent_id' => $parent?->id,
            'name' => $name,
            'slug' => Str::slug($name),
            'position' => $position,
        ]);
    }
}
