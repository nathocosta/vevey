<?php

declare(strict_types=1);

namespace Vevey\Inventory\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Vevey\Inventory\Enums\ReservationStatus;

/**
 * @property string $id
 * @property string $order_id
 * @property string $product_id
 * @property int $qty
 * @property ReservationStatus $status
 * @property Carbon $expires_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['order_id', 'product_id', 'qty', 'status', 'expires_at'])]
#[Table(name: 'stock_reservations')]
final class StockReservation extends Model
{
    use HasUuids;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'status' => ReservationStatus::class,
            'expires_at' => 'datetime',
        ];
    }
}
