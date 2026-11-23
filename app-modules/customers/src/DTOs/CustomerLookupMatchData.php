<?php

declare(strict_types=1);

namespace Vevey\Customers\DTOs;

use Vevey\Customers\Enums\IdentityType;

final readonly class CustomerLookupMatchData
{
    public function __construct(
        public CustomerData $customer,
        public IdentityType $matchedType,
        public string $matchedValue,
        public bool $isRetired,
    ) {}
}
