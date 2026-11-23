<?php

declare(strict_types=1);

namespace Vevey\Inventory\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Vevey\Inventory\Policies\StockLevelPolicy;

/**
 * Balance cache per product (ADR 2 & 5). `on_hand` is updated only through inventory actions under lockForUpdate.
 *
 * @property string $product_id
 * @property int $on_hand
 * @property int $reserved
 * @property int $available
 * @property int $reorder_point
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[UsePolicy(StockLevelPolicy::class)]
#[Fillable(['product_id', 'on_hand', 'reserved', 'reorder_point'])]
#[Table(name: 'stock_levels')]
final class StockLevel extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'product_id';

    protected $keyType = 'string';

    public function getAvailableQuantityAttribute(): int
    {
        return $this->on_hand - $this->reserved;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'on_hand' => 'integer',
            'reserved' => 'integer',
            'available' => 'integer',
            'reorder_point' => 'integer',
        ];
    }
}
