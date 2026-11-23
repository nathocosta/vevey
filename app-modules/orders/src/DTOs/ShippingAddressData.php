<?php

declare(strict_types=1);

namespace Vevey\Orders\DTOs;

final readonly class ShippingAddressData
{
    public function __construct(
        public string $cep,
        public string $street,
        public string $number,
        public string $neighborhood,
        public string $city,
        public string $state,
        public ?string $complement = null,
    ) {}
}
