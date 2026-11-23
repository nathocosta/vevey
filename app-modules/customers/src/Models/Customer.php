<?php

declare(strict_types=1);

namespace Vevey\Customers\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Vevey\Customers\Enums\IdentityType;
use Vevey\Customers\Policies\CustomerPolicy;

/**
 * Storefront customer authenticatable on the separate `customers` guard (ADR 7).
 * Email and password are nullable so WhatsApp/POS customers exist without fake credentials.
 *
 * @property string $id
 * @property string $name
 * @property string|null $email
 * @property string|null $cpf
 * @property string|null $password
 * @property string|null $remember_token
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, CustomerIdentity> $identities
 * @property-read Collection<int, CustomerIdentity> $activeIdentities
 * @property-read Collection<int, CustomerAddress> $addresses
 */
#[UsePolicy(CustomerPolicy::class)]
#[Fillable(['name', 'email', 'cpf', 'password', 'email_verified_at'])]
#[Hidden(['password', 'remember_token'])]
#[Table(name: 'customers')]
final class Customer extends Authenticatable
{
    use HasUuids;
    use Notifiable;
    use SoftDeletes;

    /**
     * @return HasMany<CustomerIdentity, $this>
     */
    public function identities(): HasMany
    {
        return $this->hasMany(CustomerIdentity::class)->orderByDesc('is_primary')->latest();
    }

    /**
     * @return HasMany<CustomerIdentity, $this>
     */
    public function activeIdentities(): HasMany
    {
        return $this->hasMany(CustomerIdentity::class)->whereNull('retired_at');
    }

    /**
     * @return HasMany<CustomerAddress, $this>
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class)->orderByDesc('is_default')->latest();
    }

    public function primaryPhone(): ?string
    {
        $identity = $this->activeIdentities()
            ->whereIn('type', [IdentityType::Whatsapp->value, IdentityType::Phone->value])
            ->orderByDesc('is_primary')
            ->first();

        return $identity?->value;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
