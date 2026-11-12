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
