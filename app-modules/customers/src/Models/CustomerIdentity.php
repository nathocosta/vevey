<?php

declare(strict_types=1);

namespace Vevey\Customers\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Vevey\Customers\Enums\IdentitySource;
use Vevey\Customers\Enums\IdentityType;

/**
 * Contact channel identity with soft retirement (ADR 7).
 *
 * @property string $id
 * @property string $customer_id
 * @property IdentityType $type
 * @property string $value
 * @property IdentitySource $source
 * @property bool $is_primary
 * @property Carbon|null $verified_at
 * @property Carbon|null $retired_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Customer $customer
 */
#[Fillable(['customer_id', 'type', 'value', 'source', 'is_primary', 'verified_at', 'retired_at'])]
#[Table(name: 'customer_identities')]
final class CustomerIdentity extends Model
{
    use HasUuids;

    public static function normalizeValue(IdentityType $type, string $raw): string
    {
        $trimmed = mb_trim($raw);

        if ($type === IdentityType::Email) {
            return mb_strtolower($trimmed);
        }

        if ($type === IdentityType::Phone || $type === IdentityType::Whatsapp) {
            $digits = (string) preg_replace('/\D+/', '', $trimmed);
            if ($digits === '') {
                return '';
            }

            if (mb_strlen($digits) === 10 || mb_strlen($digits) === 11) {
                return '+55' . $digits;
            }

            if (str_starts_with($digits, '55') && (mb_strlen($digits) === 12 || mb_strlen($digits) === 13)) {
                return '+' . $digits;
            }

            return '+' . $digits;
        }

        return ltrim(mb_strtolower($trimmed), '@');
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => IdentityType::class,
            'source' => IdentitySource::class,
            'is_primary' => 'boolean',
            'verified_at' => 'datetime',
            'retired_at' => 'datetime',
        ];
    }
}
