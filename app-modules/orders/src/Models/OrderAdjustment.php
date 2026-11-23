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
use Vevey\Orders\Enums\AdjustmentType;

/**
 * @property string $id
 * @property string $order_id
 * @property AdjustmentType $type
 * @property string $label
 * @property Money $amount_cents
 * @property array<string, mixed>|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Order $order
 */
#[Fillable(['order_id', 'type', 'label', 'amount_cents', 'metadata'])]
#[Table(name: 'order_adjustments')]
final class OrderAdjustment extends Model
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
            'type' => AdjustmentType::class,
            'amount_cents' => MoneyCast::class,
            'metadata' => 'array',
        ];
    }
}
