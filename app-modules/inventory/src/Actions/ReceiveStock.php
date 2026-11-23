<?php

declare(strict_types=1);

namespace Vevey\Inventory\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Vevey\Inventory\DTOs\ReceiveStockData;
use Vevey\Inventory\Enums\ReservationStatus;
use Vevey\Inventory\Enums\StockMovementReason;
use Vevey\Inventory\Models\StockLevel;
use Vevey\Inventory\Models\StockMovement;
use Vevey\Inventory\Models\StockReservation;

/**
 * Records a supplier purchase / inbound stock entry (`purchase` +qty) in the ledger (ADR 2).
 */
final class ReceiveStock
{
    public function handle(ReceiveStockData $data): StockLevel
    {
        if ($data->quantity <= 0) {
            throw new InvalidArgumentException('A quantidade de entrada deve ser maior que zero.');
        }

        return DB::transaction(static function () use ($data): StockLevel {
            StockLevel::query()->firstOrCreate(
                ['product_id' => $data->productId],
                ['on_hand' => 0, 'reserved' => 0, 'reorder_point' => $data->reorderPoint ?? 5],
            );

            /** @var StockLevel $level */
            $level = StockLevel::query()
                ->where('product_id', $data->productId)
                ->lockForUpdate()
                ->firstOrFail();

            $newOnHand = $level->on_hand + $data->quantity;
            $now = now();

            $movementData = [
                'product_id' => $data->productId,
                'qty' => $data->quantity,
                'reason' => StockMovementReason::Purchase,
                'balance_after' => $newOnHand,
                'user_id' => $data->userId,
                'note' => $data->note ?? 'Entrada de mercadoria.',
                'created_at' => $now,
            ];

            if (DB::getDriverName() !== 'pgsql') {
                $movementData['direction'] = 'in';
            }

            StockMovement::query()->create($movementData);

            $activeReserved = (int) StockReservation::query()
                ->where('product_id', $data->productId)
                ->where('status', ReservationStatus::Active)
                ->where('expires_at', '>', $now)
                ->sum('qty');

            $updates = [
                'on_hand' => $newOnHand,
                'reserved' => $activeReserved,
                'reorder_point' => $data->reorderPoint ?? $level->reorder_point,
            ];
            if (DB::getDriverName() !== 'pgsql') {
                $updates['available'] = $newOnHand - $activeReserved;
            }

            $level->update($updates);

            return $level->refresh();
        });
    }
}
