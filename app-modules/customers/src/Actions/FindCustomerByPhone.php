<?php

declare(strict_types=1);

namespace Vevey\Customers\Actions;

use Vevey\Customers\DTOs\CustomerData;
use Vevey\Customers\DTOs\CustomerLookupMatchData;
use Vevey\Customers\Enums\IdentityType;
use Vevey\Customers\Models\Customer;
use Vevey\Customers\Models\CustomerIdentity;

/**
 * Looks up customers by phone/WhatsApp including retired numbers (RF013 & ADR 7).
 */
final class FindCustomerByPhone
{
    /**
     * @return list<CustomerLookupMatchData>
     */
    public function handle(string $rawPhone): array
    {
        $normalized = CustomerIdentity::normalizeValue(IdentityType::Phone, $rawPhone);
        if ($normalized === '') {
            return [];
        }

        $identities = CustomerIdentity::query()
            ->whereIn('type', [IdentityType::Phone->value, IdentityType::Whatsapp->value])
            ->where('value', $normalized)
            ->with('customer')
            ->orderByRaw('case when retired_at is null then 0 else 1 end')
            ->get();

        $matches = [];
        foreach ($identities as $identity) {
            if (! $identity->customer instanceof Customer) {
                continue;
            }

            $matches[] = new CustomerLookupMatchData(
                customer: CustomerData::fromModel($identity->customer),
                matchedType: $identity->type,
                matchedValue: $identity->value,
                isRetired: $identity->retired_at !== null,
            );
        }

        return $matches;
    }
}
