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

/**
 * Snapshot of a sold product line (ADR 3).
 *
 * @property string $id
 * @property string $order_id
 * @property string $product_id
 * @property string $sku
 * @property string $name
 * @property Money $unit_price_cents
 * @property int $quantity
 * @property Money $discount_cents
 * @property Money $total_cents
 * @property array<string, mixed>|null $product_snapshot
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Order $order
 */
#[Fillable([
    'order_id',
    'product_id',
    'sku',
    'name',
    'unit_price_cents',
    'quantity',
    'discount_cents',
    'total_cents',
    'product_snapshot',
])]
#[Table(name: 'order_items')]
final class OrderItem extends Model
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
            'unit_price_cents' => MoneyCast::class,
            'quantity' => 'integer',
            'discount_cents' => MoneyCast::class,
            'total_cents' => MoneyCast::class,
            'product_snapshot' => 'array',
        ];
    }
}
