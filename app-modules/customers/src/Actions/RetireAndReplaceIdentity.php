<?php

declare(strict_types=1);

namespace Vevey\Customers\Actions;

use Illuminate\Support\Facades\DB;
use Vevey\Customers\Enums\IdentitySource;
use Vevey\Customers\Enums\IdentityType;
use Vevey\Customers\Models\CustomerIdentity;

/**
 * Retires a customer's old contact identity (keeping history searchable) and inserts the new primary one (ADR 7).
 */
final class RetireAndReplaceIdentity
{
    public function handle(
        string $customerId,
        IdentityType $type,
        string $newRawValue,
        IdentitySource $source = IdentitySource::Web,
    ): CustomerIdentity {
        $normalized = CustomerIdentity::normalizeValue($type, $newRawValue);

        return DB::transaction(static function () use ($customerId, $type, $normalized, $source): CustomerIdentity {
            CustomerIdentity::query()
                ->where('customer_id', $customerId)
                ->where('type', $type->value)
                ->whereNull('retired_at')
                ->update([
                    'is_primary' => false,
                    'retired_at' => now(),
                ]);

            return CustomerIdentity::query()->create([
                'customer_id' => $customerId,
                'type' => $type,
                'value' => $normalized,
                'source' => $source,
                'is_primary' => true,
            ]);
        });
    }
}
