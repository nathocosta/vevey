<?php

declare(strict_types=1);

namespace Vevey\Customers\Actions;

use Illuminate\Support\Facades\DB;
use Vevey\Customers\DTOs\CustomerData;
use Vevey\Customers\Enums\IdentitySource;
use Vevey\Customers\Enums\IdentityType;
use Vevey\Customers\Models\Customer;
use Vevey\Customers\Models\CustomerIdentity;

/**
 * Finds a customer by active email or phone identity, or creates a passwordless customer record (ADR 7).
 * Never generates fake passwords; the customer can activate their login later with the same email.
 */
final class FindOrCreateCustomerByContact
{
    public function handle(
        string $name,
        ?string $email = null,
        ?string $phone = null,
        IdentitySource $source = IdentitySource::Web,
    ): CustomerData {
        $normalizedEmail = $email !== null && mb_trim($email) !== ''
            ? CustomerIdentity::normalizeValue(IdentityType::Email, $email)
            : null;

        $normalizedPhone = $phone !== null && mb_trim($phone) !== ''
            ? CustomerIdentity::normalizeValue(IdentityType::Whatsapp, $phone)
            : null;

        return DB::transaction(static function () use ($name, $normalizedEmail, $normalizedPhone, $source): CustomerData {
            $customer = null;

            if ($normalizedEmail !== null) {
                $customer = Customer::query()
                    ->whereRaw('lower(email) = ?', [$normalizedEmail])
                    ->first();
            }

            if (! $customer instanceof Customer && $normalizedPhone !== null) {
                $identity = CustomerIdentity::query()
                    ->whereIn('type', [IdentityType::Whatsapp->value, IdentityType::Phone->value])
                    ->where('value', $normalizedPhone)
                    ->whereNull('retired_at')
                    ->first();

                $customer = $identity?->customer;
            }

            if (! $customer instanceof Customer) {
                $customer = Customer::query()->create([
                    'name' => mb_trim($name),
                    'email' => $normalizedEmail,
                ]);
            }

            if ($normalizedEmail !== null) {
                self::attachIdentityIfAvailable($customer, IdentityType::Email, $normalizedEmail, $source);
            }

            if ($normalizedPhone !== null) {
                self::attachIdentityIfAvailable($customer, IdentityType::Whatsapp, $normalizedPhone, $source);
            }

            return CustomerData::fromModel($customer);
        });
    }

    private static function attachIdentityIfAvailable(
        Customer $customer,
        IdentityType $type,
        string $normalizedValue,
        IdentitySource $source,
    ): void {
        if ($normalizedValue === '') {
            return;
        }

        $activeOwner = CustomerIdentity::query()
            ->where('type', $type->value)
            ->where('value', $normalizedValue)
            ->whereNull('retired_at')
            ->first();

        if ($activeOwner instanceof CustomerIdentity) {
            return;
        }

        $hasPrimary = CustomerIdentity::query()
            ->where('customer_id', $customer->id)
            ->where('type', $type->value)
            ->where('is_primary', true)
            ->whereNull('retired_at')
            ->exists();

        CustomerIdentity::query()->create([
            'customer_id' => $customer->id,
            'type' => $type,
            'value' => $normalizedValue,
            'source' => $source,
            'is_primary' => ! $hasPrimary,
        ]);
    }
}
