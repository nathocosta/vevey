<?php

declare(strict_types=1);

namespace Vevey\Customers\DTOs;

use Vevey\Customers\Models\Customer;

final readonly class CustomerData
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $email,
        public ?string $cpf,
        public ?string $primaryPhone,
    ) {}

    public static function fromModel(Customer $customer): self
    {
        return new self(
            id: $customer->id,
            name: $customer->name,
            email: $customer->email,
            cpf: $customer->cpf,
            primaryPhone: $customer->primaryPhone(),
        );
    }
}
