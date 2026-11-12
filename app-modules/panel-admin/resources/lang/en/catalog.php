<?php

declare(strict_types=1);

return [
    'navigation_group' => 'Catalog',
    'products' => [
        'label' => 'Product',
        'plural_label' => 'Products',
        'sections' => [
            'details' => 'Product details',
            'prices' => 'Prices',
            'classification' => 'Categories and attributes',
            'images' => 'Images',
        ],
        'duplicate' => [
            'label' => 'Duplicate',
            'copy_suffix' => 'copy',
            'heading' => 'Duplicate product',
        ],
    ],
    'categories' => [
        'label' => 'Category',
        'plural_label' => 'Categories',
        'parent_help' => 'Only top-level categories can be a parent.',
    ],
    'attributes' => [
        'label' => 'Attribute',
        'plural_label' => 'Attributes',
        'values' => 'Values',
        'add_value' => 'Add value',
    ],
    'fields' => [
        'name' => 'Name',
        'slug' => 'Slug',
        'sku' => 'SKU',
        'barcode' => 'Barcode',
        'description' => 'Description',
        'is_active' => 'Active',
        'price' => 'Price',
        'promo_price' => 'Promotional price',
        'categories' => 'Categories',
        'attribute_values' => 'Attributes',
        'parent' => 'Parent category',
        'position' => 'Order',
        'value' => 'Value',
        'updated_at' => 'Updated at',
    ],
];
