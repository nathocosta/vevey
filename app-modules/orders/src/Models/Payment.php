<?php

declare(strict_types=1);

namespace Vevey\Orders\Models;

use App\Casts\MoneyCast;
use Brick\Money\Money;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Vevey\Orders\Enums\PaymentMethod;
use Vevey\Orders\Enums\PaymentStatus;

/**
 * @property string $id
 * @property string $order_id
 * @property PaymentMethod $method
 * @property string $provider
 * @property string|null $provider_id
 * @property Money $amount_cents
 * @property PaymentStatus $status
 * @property Carbon|null $expires_at
 * @property Carbon|null $paid_at
 * @property array<string, mixed>|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Order $order
 */
#[Fillable([
    'order_id',
    'method',
    'provider',
    'provider_id',
    'amount_cents',
    'status',
    'expires_at',
    'paid_at',
    'metadata',
])]
#[Table(name: 'payments')]
final class Payment extends Model
{
    use HasUuids;

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'method' => PaymentMethod::class,
            'amount_cents' => MoneyCast::class,
            'status' => PaymentStatus::class,
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
