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

        /** @var list<array{sku: string, name: string, description: string, price: int, promo?: int, barcode?: string, categories: list<Category>, values: list<string>, active?: bool}> $products */
        $products = [
            ['sku' => 'BAL-MET-9-AZ', 'name' => 'Balão metalizado 9" azul', 'description' => 'Balão metalizado de 9 polegadas com acabamento brilhante em azul royal. Ideal para arcos desconstruídos, centros de mesa e decorações de aniversário.', 'price' => 1250, 'promo' => 990, 'barcode' => '7891234567895', 'categories' => [$balloons], 'values' => ['Cor:Azul', 'Tipo:Metalizado', 'Tamanho:9 polegadas']],
            ['sku' => 'BAL-MET-9-RS', 'name' => 'Balão metalizado 9" rosa', 'description' => 'Balão metalizado de 9 polegadas em tom rosa perolado, com válvula autosselante para inflar com ar ou gás hélio. Perfeito para festas infantis e chás.', 'price' => 1250, 'categories' => [$balloons], 'values' => ['Cor:Rosa', 'Tipo:Metalizado', 'Tamanho:9 polegadas']],
            ['sku' => 'BAL-MET-9-DR', 'name' => 'Balão metalizado 9" dourado', 'description' => 'Balão metalizado de 9 polegadas dourado espelhado. Destaque sofisticado para formaturas, casamentos e comemorações de fim de ano.', 'price' => 1390, 'categories' => [$balloons], 'values' => ['Cor:Dourado', 'Tipo:Metalizado', 'Tamanho:9 polegadas']],
            ['sku' => 'BAL-LTX-11-AZ', 'name' => 'Balão látex 11" azul (pacote com 50)', 'description' => 'Pacote econômico com 50 balões de látex profissional nº 11 em cor azul intensa. Alta resistência e uniformidade para painéis e guirlandas.', 'price' => 2490, 'categories' => [$balloons], 'values' => ['Cor:Azul', 'Tipo:Látex', 'Tamanho:11 polegadas']],
            ['sku' => 'BAL-LTX-11-BR', 'name' => 'Balão látex 11" branco (pacote com 50)', 'description' => 'Pacote com 50 balões de látex nº 11 branco fosco de alta durabilidade, ideal para combinar com qualquer paleta de cores.', 'price' => 2490, 'categories' => [$balloons], 'values' => ['Cor:Branco', 'Tipo:Látex', 'Tamanho:11 polegadas'], 'active' => false],
            ['sku' => 'PRT-DESC-AZ', 'name' => 'Prato descartável azul (10 unidades)', 'description' => 'Conjunto com 10 pratos descartáveis resistentes (15 cm) na cor azul, perfeitos para servir bolos, docinhos e salgados em festas.', 'price' => 890, 'categories' => [$disposables], 'values' => ['Cor:Azul']],
            ['sku' => 'PRT-DESC-RS', 'name' => 'Prato descartável rosa (10 unidades)', 'description' => 'Conjunto com 10 pratos descartáveis em rosa suave, fabricados em poliestireno firme que não dobra ao servir sobremesas.', 'price' => 890, 'categories' => [$disposables], 'values' => ['Cor:Rosa']],
            ['sku' => 'COP-DESC-DR', 'name' => 'Copo descartável dourado (10 unidades)', 'description' => 'Pacote com 10 copos descartáveis de 200 ml com acabamento dourado elegante para harmonizar com a mesa principal da festa.', 'price' => 1090, 'categories' => [$disposables], 'values' => ['Cor:Dourado']],
            ['sku' => 'DOC-KITKAT-CX24', 'name' => 'Caixa KitKat ao Leite 41,5g (24 unidades)', 'description' => 'Display fechado com 24 unidades de KitKat Nestlé ao leite (41,5g cada). Wafer crocante coberto com chocolate ao leite — ideal para revenda, lembrancinhas ou mesas de doces.', 'price' => 7490, 'promo' => 6490, 'categories' => [$chocolates], 'values' => []],
            ['sku' => 'DOC-BIS-CX20', 'name' => 'Caixa Lacta Bis Ao Leite 100,8g (20 unidades)', 'description' => 'Caixa fechada no atacado com 20 bandejas de Lacta Bis Ao Leite (100,8g cada). Excelente custo-benefício para festas, eventos corporativos e pontos de revenda.', 'price' => 11900, 'promo' => 10490, 'categories' => [$chocolates], 'values' => []],
            ['sku' => 'DOC-BALA-FINI-500', 'name' => 'Balas Fini Beijos de Morango (pacote 500g)', 'description' => 'Pacote de 500g de balas de gelatina Fini Beijos sabor morango com nata. Textura macia e sabor marcante para potes de mesa e kits de lembrancinha.', 'price' => 2690, 'categories' => [$candies], 'values' => []],
            ['sku' => 'DOC-PIRU-SORT-50', 'name' => 'Pirulito Pop Sortido (pacote com 50 unidades)', 'description' => 'Pacote econômico com 50 pirulitos sortidos nos sabores morango, uva, maçã verde e tutti-frutti. Embalados individualmente, prontos para saquinhos surpresa.', 'price' => 2190, 'categories' => [$candies], 'values' => []],
            ['sku' => 'DOC-OURO-PC1KG', 'name' => 'Bombom Lacta Ouro Branco (pacote 1kg)', 'description' => 'Pacote de 1kg com bombons Lacta Ouro Branco (aprox. 50 unidades). Recheio cremoso de chocolate branco com flocos crocantes e cobertura branca irresistível — clássico das festas brasileiras.', 'price' => 6290, 'promo' => 5490, 'categories' => [$chocolates], 'values' => []],
            ['sku' => 'EMB-PAP-DOCE-25', 'name' => 'Caixa de papelão kraft para doces e kits (25 unidades)', 'description' => 'Pacote com 25 caixas desmontadas em papelão kraft reforçado de montagem rápida por encaixe. Ideal para entrega de doces, brownies, kits festa e confeitaria.', 'price' => 3890, 'categories' => [$packaging, $party], 'values' => []],
        ];

        foreach ($products as $data) {
            $product = Product::withTrashed()->updateOrCreate(
                ['sku' => $data['sku']],
                [
                    'barcode' => $data['barcode'] ?? null,
                    'name' => $data['name'],
                    'slug' => Str::slug($data['name']),
                    'description' => $data['description'],
                    'price_cents' => Money::ofMinor($data['price'], MoneyCast::CURRENCY),
                    'promo_price_cents' => isset($data['promo']) ? Money::ofMinor($data['promo'], MoneyCast::CURRENCY) : null,
                    'is_active' => $data['active'] ?? true,
                ],
            );

            $product->categories()->syncWithoutDetaching(array_map(fn (Category $category): string => $category->id, $data['categories']));
            $product->attributeValues()->syncWithoutDetaching(array_map(fn (string $key): string => $values[$key]->id, $data['values']));
        }

        $softDeleted = Product::query()->where('sku', 'BAL-MET-9-DR')->first();
        $softDeleted?->delete();
    }

    /**
     * @param  array<string, list<string>>  $attributes
     * @return array<string, AttributeValue> keyed by "Attribute:Value"
     */
    private function seedAttributes(array $attributes): array
    {
        $values = [];

        foreach ($attributes as $name => $attributeValues) {
            $attribute = Attribute::query()->firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            );

            foreach ($attributeValues as $value) {
                $values["{$name}:{$value}"] = $attribute->values()->firstOrCreate(
                    ['slug' => Str::slug($value)],
                    ['value' => $value],
                );
            }
        }

        return $values;
    }

    private function category(string $name, ?Category $parent = null, int $position = 0): Category
    {
        return Category::query()->firstOrCreate(
            ['slug' => Str::slug($name)],
            [
                'parent_id' => $parent?->id,
                'name' => $name,
                'position' => $position,
            ],
        );
    }
}
