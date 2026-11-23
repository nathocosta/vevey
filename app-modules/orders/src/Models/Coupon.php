<?php

declare(strict_types=1);

namespace Vevey\Orders\Models;

use App\Casts\MoneyCast;
use Brick\Money\Money;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Vevey\Orders\Enums\CouponType;
use Vevey\Orders\Policies\CouponPolicy;

/**
 * @property string $id
 * @property string $code
 * @property CouponType $type
 * @property int $value
 * @property Money $min_subtotal_cents
 * @property int|null $max_uses
 * @property int $uses_count
 * @property bool $is_active
 * @property Carbon|null $starts_at
 * @property Carbon|null $expires_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[UsePolicy(CouponPolicy::class)]
#[Fillable(['code', 'type', 'value', 'min_subtotal_cents', 'max_uses', 'uses_count', 'is_active', 'starts_at', 'expires_at'])]
#[Table(name: 'coupons')]
final class Coupon extends Model
{
    use HasUuids;

    public function isValidFor(Money $subtotal): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at !== null && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->expires_at !== null && $now->gt($this->expires_at)) {
            return false;
        }

        if ($this->max_uses !== null && $this->uses_count >= $this->max_uses) {
            return false;
        }

        return ! $subtotal->isLessThan($this->min_subtotal_cents);
    }

    public function calculateDiscount(Money $subtotal): Money
    {
        if (! $this->isValidFor($subtotal)) {
            return Money::ofMinor(0, MoneyCast::CURRENCY);
        }

        $subtotalCents = $subtotal->getMinorAmount()->toInt();

        $discountCents = match ($this->type) {
            CouponType::Percent => (int) round($subtotalCents * min(100, $this->value) / 100),
            CouponType::Fixed => min($subtotalCents, $this->value),
        };

        return Money::ofMinor(max(0, $discountCents), MoneyCast::CURRENCY);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CouponType::class,
            'value' => 'integer',
            'min_subtotal_cents' => MoneyCast::class,
            'max_uses' => 'integer',
            'uses_count' => 'integer',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }
}
