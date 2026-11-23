<?php

declare(strict_types=1);

namespace Vevey\Inventory\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Vevey\Inventory\Enums\StockMovementReason;
use Vevey\Inventory\Policies\StockMovementPolicy;

/**
 * Append-only single-entry stock ledger (ADR 2). Never updated or deleted.
 *
 * @property string $id
 * @property string $product_id
 * @property int $qty
 * @property string|null $direction
 * @property StockMovementReason $reason
 * @property string|null $reference_type
 * @property string|null $reference_id
 * @property int $balance_after
 * @property string|null $user_id
 * @property string|null $note
 * @property Carbon $created_at
 * @property-read User|null $user
 */
#[UsePolicy(StockMovementPolicy::class)]
#[Fillable([
    'product_id',
    'qty',
    'reason',
    'reference_type',
    'reference_id',
    'balance_after',
    'user_id',
    'note',
    'created_at',
])]
#[Table(name: 'stock_movements')]
final class StockMovement extends Model
{
    use HasUuids;

    public const null UPDATED_AT = null;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'reason' => StockMovementReason::class,
            'balance_after' => 'integer',
            'created_at' => 'datetime',
        ];
    }
}
