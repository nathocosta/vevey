<?php

declare(strict_types=1);

return [
    'navigation_group' => 'Catálogo',
    'products' => [
        'label' => 'Produto',
        'plural_label' => 'Produtos',
        'sections' => [
            'details' => 'Dados do produto',
            'prices' => 'Preços',
            'classification' => 'Categorias e atributos',
            'images' => 'Imagens',
        ],
        'duplicate' => [
            'label' => 'Duplicar',
            'copy_suffix' => 'cópia',
            'heading' => 'Duplicar produto',
        ],
    ],
    'categories' => [
        'label' => 'Categoria',
        'plural_label' => 'Categorias',
        'parent_help' => 'Só categorias de primeiro nível podem ser pai.',
    ],
    'attributes' => [
        'label' => 'Atributo',
        'plural_label' => 'Atributos',
        'values' => 'Valores',
        'add_value' => 'Adicionar valor',
    ],
    'in_use' => [
        'category' => '«:name» ainda tem :products produto(s) e :children subcategoria(s). Mova-os para outra categoria antes de excluir.',
        'attribute' => 'Os valores de «:name» estão em uso: :values. Tire esses valores dos produtos antes de excluir o atributo.',
        'attribute_values' => 'Não dá para remover valores em uso: :values. Tire esses valores dos produtos antes de remover.',
        'value_usage' => ':value (:count produto(s))',
    ],
    'fields' => [
        'name' => 'Nome',
        'slug' => 'Slug',
        'sku' => 'SKU',
        'barcode' => 'Código de barras',
        'description' => 'Descrição',
        'is_active' => 'Ativo',
        'price' => 'Preço',
        'promo_price' => 'Preço promocional',
        'categories' => 'Categorias',
        'attribute_values' => 'Atributos',
        'parent' => 'Categoria pai',
        'position' => 'Ordem',
        'value' => 'Valor',
        'updated_at' => 'Atualizado em',
    ],
];
