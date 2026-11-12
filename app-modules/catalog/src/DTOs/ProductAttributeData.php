<?php

declare(strict_types=1);

namespace Vevey\Catalog\DTOs;

use Vevey\Catalog\Models\AttributeValue;

/**
 * One attribute of a product as shown to the customer, e.g. Cor: Azul.
 */
final readonly class ProductAttributeData
{
    public function __construct(
        public string $attribute,
        public string $value,
    ) {}

    public static function fromModel(AttributeValue $attributeValue): self
    {
        return new self(
            attribute: $attributeValue->attribute->name,
            value: $attributeValue->value,
        );
    }
}
